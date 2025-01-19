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

namespace Sunrise\Http\Router\OpenApi\Command;

use Fig\Http\Message\StatusCodeInterface;
use JsonException;
use ReflectionAttribute;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Annotation\RequestHeader;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\Helper\RouteBuilder;
use Sunrise\Http\Router\Helper\RouteParser;
use Sunrise\Http\Router\Helper\RouteSimplifier;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverChainInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RequestHandlerReflectorInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_merge;
use function file_put_contents;
use function in_array;
use function json_encode;
use function strtolower;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @since 3.0.0
 */
#[AsCommand('router:generate-open-api-document', 'Generates OpenAPI Document.')]
final class RouterGenerateOpenApiDocumentCommand extends Command
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly OpenApiConfiguration $openApiConfiguration,
        private readonly PhpTypeSchemaResolverChainInterface $phpTypeSchemaResolverChain,
        private readonly RequestHandlerReflectorInterface $requestHandlerReflector,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     *
     * @throws JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $document = $this->openApiConfiguration->blankDocument;

        foreach ($this->router->getRoutes() as $route) {
            if (!$route->isApiOperation()) {
                continue;
            }

            $requestHandler = $this->requestHandlerReflector->reflectRequestHandler($route->getRequestHandler());

            self::enrichDocumentWithPaths($route, $requestHandler, $document);
        }

        foreach ($this->phpTypeSchemaResolverChain->getNamedPhpTypeSchemas() as $phpTypeSchemaName => $phpTypeSchema) {
            $document['components']['schemas'][$phpTypeSchemaName] = $phpTypeSchema;
        }

        file_put_contents(
            $this->openApiConfiguration->getDocumentFilename(),
            json_encode($document, JSON_THROW_ON_ERROR),
        );

        $output->writeln('Done.');

        return self::SUCCESS;
    }

    private function enrichDocumentWithPaths(
        RouteInterface $route,
        ReflectionMethod $controller,
        array &$document,
    ): void {
        $operation = (array) $route->getApiOperationDocFields();
        $operation['operationId'] = $route->getName();
        $operation['tags'] = $route->getTags();
        $operation['summary'] = $route->getSummary();
        $operation['description'] = $route->getDescription();

        if ($route->isDeprecated()) {
            $operation['deprecated'] = true;
        }

        self::enrichOperationWithPathParameters($route, $operation);
        self::enrichOperationWithCookieAndHeaderParameters($controller, $operation);

        self::enrichOperationWithRequestBody($route, $controller, $operation);
        self::enrichOperationWithEmptyResponses($route, $controller, $operation);
        self::enrichOperationWithEncodableResponses($route, $controller, $operation);

        $routePath = RouteSimplifier::simplifyRoute($route->getPath());
        foreach ($route->getMethods() as $routeMethod) {
            $routeMethod = strtolower($routeMethod);
            $document['paths'][$routePath][$routeMethod] = $operation;
        }
    }

    private static function enrichOperationWithPathParameters(RouteInterface $route, array &$operation): void
    {
        $routeVars = RouteParser::parseRoute($route->getPath());
        $routePatterns = $route->getPatterns();
        $routeAttributes = $route->getAttributes();

        foreach ($routeVars as $routeVar) {
            $parameter = [];
            $parameter['in'] = 'path';
            $parameter['name'] = $routeVar['name'];
            $parameter['schema']['type'] = 'string';

            $pattern = $routeVar['pattern'] ?? $routePatterns[$routeVar['name']] ?? null;
            if ($pattern !== null) {
                $parameter['schema']['pattern'] = '^' . $pattern . '$';
            }

            if (isset($routeAttributes[$routeVar['name']])) {
                $default = RouteBuilder::stringifyValue($routeAttributes[$routeVar['name']]);
                $parameter['schema']['default'] = $default;
            }

            if (!isset($routeVar['optional_part'])) {
                $parameter['required'] = true;
            }

            $operation['parameters'][] = $parameter;
        }
    }

    private static function enrichOperationWithCookieAndHeaderParameters(
        ReflectionMethod $endpoint,
        array &$operation,
    ): void {
        foreach ($endpoint->getParameters() as $endpointParameter) {
            /** @var ReflectionAttribute $annotations */
            $annotations = array_merge(
                $endpointParameter->getAttributes(RequestCookie::class),
                $endpointParameter->getAttributes(RequestHeader::class),
            );

            foreach ($annotations as $annotation) {
                $annotation = $annotation->newInstance();

                $operationParameter = [];

                $operationParameter['in'] = match ($annotation::class) {
                    RequestCookie::class => 'cookie',
                    RequestHeader::class => 'header',
                };

                $operationParameter['name'] = $annotation->name;
                $operationParameter['schema']['type'] = 'string';

                if (!$endpointParameter->isDefaultValueAvailable()) {
                    $operationParameter['required'] = true;
                }

                $operation['parameters'][] = $operationParameter;
            }
        }
    }

    private function enrichOperationWithRequestBody(
        RouteInterface $route,
        ReflectionMethod $controller,
        array &$operation,
    ): void {
        foreach ($controller->getParameters() as $parameter) {
            if ($parameter->getAttributes(RequestBody::class) === []) {
                continue;
            }

            $schema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema(
                TypeFactory::fromPhpTypeReflection($parameter->getType()),
                $parameter,
            );

            foreach ($route->getConsumedMediaTypes() as $mediaType) {
                $operation['requestBody']['content'][$mediaType->getIdentifier()]['schema'] = $schema;
            }

            return;
        }
    }

    private function enrichOperationWithEmptyResponses(
        RouteInterface $route,
        ReflectionMethod $responder,
        array &$operation,
    ): void {
        if (
            ! in_array($responder->getReturnType()?->getName(), [
                Type::PHP_TYPE_NAME_VOID,
                Type::PHP_TYPE_NAME_NULL,
            ], true)
        ) {
            return;
        }

        $responseCode = $this->openApiConfiguration->defaultNullResponseStatusCode;

        /** @var ReflectionAttribute $annotations */
        $annotations = $responder->getAttributes(ResponseStatus::class);
        if (isset($annotations[0])) {
            $responseStatus = $annotations[0]->newInstance();
            $responseCode = $responseStatus->code;
        }

        // phpcs:ignore Generic.Files.LineLength.TooLong
        $operation['responses'][$responseCode]['description'] = $this->openApiConfiguration->completedOperationDescription;
    }

    private function enrichOperationWithEncodableResponses(
        RouteInterface $route,
        ReflectionMethod $responder,
        array &$operation,
    ): void {
        if ($responder->getAttributes(EncodableResponse::class) === []) {
            return;
        }

        $responseCode = StatusCodeInterface::STATUS_OK;
        $responseType = TypeFactory::fromPhpTypeReflection($responder->getReturnType());
        $responseSchema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema($responseType, $responder);

        /** @var ReflectionAttribute $annotations */
        $annotations = $responder->getAttributes(ResponseStatus::class);
        if (isset($annotations[0])) {
            $responseStatus = $annotations[0]->newInstance();
            $responseCode = $responseStatus->code;
        }

        // phpcs:ignore Generic.Files.LineLength.TooLong
        $operation['responses'][$responseCode]['description'] = $this->openApiConfiguration->completedOperationDescription;

        foreach ($route->getProducedMediaTypes() as $mediaType) {
            $operation['responses'][$responseCode]['content'][$mediaType->getIdentifier()]['schema'] = $responseSchema;
        }
    }
}
