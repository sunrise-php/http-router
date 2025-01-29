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

use ReflectionAttribute;
use RuntimeException;
use Sunrise\Http\Router\CodecManagerInterface;
use Sunrise\Http\Router\Helper\ReflectorHelper;
use Sunrise\Http\Router\Helper\RouteSimplifier;
use Sunrise\Http\Router\OpenApi\Annotation\Operation;
use Sunrise\Http\Router\RequestHandlerReflectorInterface;
use Sunrise\Http\Router\RouteInterface;
use Throwable;

use function array_replace_recursive;
use function dirname;
use function file_put_contents;
use function fopen;
use function is_readable;
use function is_writable;
use function strtolower;

/**
 * @link https://spec.openapis.org/oas/v3.1.0
 *
 * @since 3.0.0
 */
final class OpenApiDocumentManager implements OpenApiDocumentManagerInterface
{
    public function __construct(
        private readonly OpenApiConfiguration $openApiConfiguration,
        private readonly OpenApiOperationEnricherManagerInterface $openApiOperationEnricherManager,
        private readonly OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager,
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

        $this->openApiPhpTypeSchemaResolverManager->enrichDocumentWithDefinitions($document);

        return $document;
    }

    /**
     * @inheritDoc
     */
    public function saveDocument(array $document): void
    {
        $filename = $this->openApiConfiguration->getDocumentFilename();
        if (!is_writable(dirname($filename))) {
            throw new RuntimeException('The directory for the OpenAPI document is not writable.');
        }

        $contents = $this->codecManager->encode(
            $this->openApiConfiguration->documentMediaType,
            $document,
            $this->openApiConfiguration->documentEncodingContext,
        );

        try {
            $result = @file_put_contents($filename, $contents);
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
        $filename = $this->openApiConfiguration->getDocumentFilename();
        if (!is_readable($filename)) {
            throw new RuntimeException('The OpenAPI document was not saved or is unavailable.');
        }

        try {
            $result = @fopen($filename, $this->openApiConfiguration->documentReadMode);
        } catch (Throwable) {
            $result = false;
        }

        if ($result === false) {
            throw new RuntimeException('The OpenAPI document could not be read.');
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $document
     * @param-out array<array-key, mixed> $document
     */
    private function describeRoute(RouteInterface $route, array &$document): void
    {
        $operation = $this->openApiConfiguration->initialOperation;
        $requestHandler = $this->requestHandlerReflector->reflectRequestHandler($route->getRequestHandler());
        $this->openApiOperationEnricherManager->enrichOperation($route, $requestHandler, $operation);

        foreach (ReflectorHelper::getAncestry($requestHandler) as $member) {
            /** @var ReflectionAttribute<Operation> $annotation */
            foreach ($member->getAttributes(Operation::class, ReflectionAttribute::IS_INSTANCEOF) as $annotation) {
                $operation = array_replace_recursive($operation, $annotation->newInstance()->value);
            }
        }

        $operation['operationId'] = $route->getName();
        $operation['tags'] = $route->getTags();
        $operation['summary'] = $route->getSummary();
        $operation['description'] = $route->getDescription();

        if ($route->isDeprecated()) {
            $operation['deprecated'] = true;
        }

        $path = RouteSimplifier::simplifyRoute($route->getPath());
        foreach ($route->getMethods() as $method) {
            $document['paths'][$path][strtolower($method)] = $operation;
        }
    }
}
