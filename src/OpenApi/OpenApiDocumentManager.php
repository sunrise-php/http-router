<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\OpenApi;

use Psr\Http\Message\StreamInterface;
use ReflectionAttribute;
use ReflectionMethod;
use RuntimeException;
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\CodecManagerInterface;
use Sunrise\Http\Router\Helper\ReflectorHelper;
use Sunrise\Http\Router\Helper\RouteSimplifier;
use Sunrise\Http\Router\OpenApi\Annotation\Operation;
use Sunrise\Http\Router\RequestHandlerReflectorInterface;
use Sunrise\Http\Router\RouteInterface;
use Throwable;

use function array_map;
use function array_merge;
use function array_merge_recursive;
use function dirname;
use function file_put_contents;
use function fopen;
use function implode;
use function is_readable;
use function is_writable;
use function strtolower;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

/**
 * @link https://spec.openapis.org/oas/v3.1.0
 *
 * @since 3.0.0
 */
final class OpenApiDocumentManager implements OpenApiDocumentManagerInterface
{
    public function __construct(
        private readonly OpenApiConfiguration $openApiConfiguration,
        private readonly PhpTypeSchemaResolverChainInterface $phpTypeSchemaResolverChain,
        private readonly RequestHandlerReflectorInterface $requestHandlerReflector,
        private readonly CodecManagerInterface $codecManager,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function buildDocument(array $routes): array
    {
        $document = $this->openApiConfiguration->initialDocument;

        foreach ($routes as $route) {
            $this->describeRoute($route, $document);
        }

        $this->phpTypeSchemaResolverChain->propagateNamedPhpTypeSchemas($document);

        return $document;
    }

    /**
     * @inheritDoc
     */
    public function saveDocument(array $document): void
    {
        if (!is_writable($this->getDocumentDirname())) {
            throw new RuntimeException('The directory for the OpenAPI document is not writable.');
        }

        $encodedDocument = $this->codecManager->encode(
            $this->openApiConfiguration->documentMediaType,
            $document,
            $this->openApiConfiguration->documentEncodingContext,
        );

        try {
            $result = @file_put_contents($this->getDocumentFilename(), $encodedDocument);
        } catch (Throwable) {
            $result = false;
        }

        if ($result === false) {
            throw new RuntimeException('The OpenAPI document could not be saved.');
        }
    }

    /**
     * @inheritDoc
     */
    public function openDocument()
    {
        if (!is_readable($this->getDocumentFilename())) {
            throw new RuntimeException('The OpenAPI document was not saved or is unavailable.');
        }

        try {
            $result = @fopen($this->getDocumentFilename(), $this->openApiConfiguration->documentReadMode);
        } catch (Throwable) {
            $result = false;
        }

        if ($result === false) {
            throw new RuntimeException('The OpenAPI document could not be read.');
        }

        return $result;
    }

    private function getDocumentDirname(): string
    {
        return dirname($this->getDocumentFilename());
    }

    private function getDocumentFilename(): string
    {
        return $this->openApiConfiguration->documentFilename ?? $this->getTemporaryDocumentFilename();
    }

    private function getTemporaryDocumentFilename(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->openApiConfiguration->temporaryDocumentBasename;
    }

    private function describeRoute(RouteInterface $route, array &$document): void
    {
        /** @var array{tags?: string[], summary?: string|string[], description?: string|string[]} $operation */
        $operation = [];

        $requestHandler = $this->requestHandlerReflector->reflectRequestHandler($route->getRequestHandler());

        foreach (ReflectorHelper::getAncestralAnnotations($requestHandler, Operation::class) as $annotation) {
            $operation = array_merge_recursive($operation, $annotation->value);
        }

        $operation = array_merge_recursive($operation, (array) $route->getDocFields());

        $operation['operationId'] = $route->getName();

        $operation['tags'] ??= [];
        $operation['tags'] = array_merge($operation['tags'], $route->getTags());

        $operation['summary'] = (array) ($operation['summary'] ?? []);
        $operation['summary'][] = $route->getSummary();
        $summarySeparator = $this->openApiConfiguration->operationSummarySeparator;
        $operation['summary'] = implode($summarySeparator, $operation['summary']);

        $operation['description'] = (array) ($operation['description'] ?? []);
        $operation['description'][] = $route->getDescription();
        $descriptionSeparator = $this->openApiConfiguration->operationDescriptionSeparator;
        $operation['description'] = implode($descriptionSeparator, $operation['description']);

        $operation['deprecated'] = $route->isDeprecated();

        if ($requestHandler instanceof ReflectionMethod) {
            $this->describeRequestBody($route, $requestHandler, $operation);
            $this->describeSuccessfulResponses($route, $requestHandler, $operation);
            $this->describeUnsuccessfulResponses($route, $requestHandler, $operation);
        }

        $path = RouteSimplifier::simplifyRoute($route->getPath());
        $methods = array_map(strtolower(...), $route->getMethods());

        foreach ($methods as $method) {
            $document['paths'][$path][$method] = $operation;
        }
    }































    private function describeRequestBody(
        RouteInterface $route,
        ReflectionMethod $reflectedRequestHandler,
        array &$operation,
    ): void {
        $consumedMediaTypes = $route->getConsumedMediaTypes();
        if ($consumedMediaTypes === []) {
            return;
        }

        $requestBodySchema = [];

        foreach ($reflectedRequestHandler->getParameters() as $requestHandlerParameter) {
            if (
                $requestHandlerParameter->getAttributes(RequestBody::class) !== [] ||
                $requestHandlerParameter->getType()?->getName() === StreamInterface::class
            ) {
                $requestBodyType = TypeFactory::fromPhpTypeReflection($requestHandlerParameter->getType());
                $requestBodySchema = $this->phpTypeSchemaResolverChain
                    ->resolvePhpTypeSchema($requestBodyType, $requestHandlerParameter);

                break;
            }
        }

        $operation['requestBody']['required'] = true;

        foreach ($consumedMediaTypes as $mediaType) {
            $operation['requestBody']['content'][$mediaType->getIdentifier()]['schema'] = $requestBodySchema;
        }
    }

    private function describeSuccessfulResponses(
        RouteInterface $route,
        ReflectionMethod $reflectedRequestHandler,
        array &$operation,
    ): void {
        $responseStatusCode = $this->openApiConfiguration->defaultSuccessfulResponseStatusCode;
        $responseSchema = [];

        if ((string) $reflectedRequestHandler->getReturnType() === Type::PHP_TYPE_NAME_VOID) {
            $responseStatusCode = $this->openApiConfiguration->defaultEmptyResponseStatusCode;
        } elseif ($reflectedRequestHandler->getAttributes(EncodableResponse::class) !== []) {
            $responseType = TypeFactory::fromPhpTypeReflection($reflectedRequestHandler->getReturnType());
            $responseSchema = $this->phpTypeSchemaResolverChain
                ->resolvePhpTypeSchema($responseType, $reflectedRequestHandler);
        }

        /** @var list<ReflectionAttribute<ResponseStatus>> $annotations */
        $annotations = $reflectedRequestHandler->getAttributes(ResponseStatus::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $responseStatusCode = $annotation->code;
        }

        $operation['responses'][$responseStatusCode]['description'] = $this->openApiConfiguration
            ->successfulResponseDescription;

        foreach ($route->getProducedMediaTypes() as $mediaType) {
            // phpcs:disable Generic.Files.LineLength.TooLong
            $operation['responses'][$responseStatusCode]['content'][$mediaType->getIdentifier()]['schema'] = $responseSchema;
        }
    }

    private function describeUnsuccessfulResponses(
        RouteInterface $route,
        ReflectionMethod $reflectedRequestHandler,
        array &$operation,
    ): void {
        if ($this->openApiConfiguration->unsuccessfulResponseViewName === null) {
            return;
        }

        $producedMediaTypes = $route->getProducedMediaTypes();
        if ($producedMediaTypes === []) {
            return;
        }

        $responseType = new Type($this->openApiConfiguration->unsuccessfulResponseViewName, allowsNull: false);
        $responseSchema = $this->phpTypeSchemaResolverChain
            ->resolvePhpTypeSchema($responseType, $reflectedRequestHandler);

        $operation['responses']['default']['description'] = $this->openApiConfiguration
            ->unsuccessfulResponseDescription;

        foreach ($producedMediaTypes as $mediaType) {
            $operation['responses']['default']['content'][$mediaType->getIdentifier()]['schema'] = $responseSchema;
        }
    }
}
