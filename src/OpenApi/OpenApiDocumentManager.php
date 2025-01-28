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
            $this->enrichDocumentWithOperations($route, $document);
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
    private function enrichDocumentWithOperations(RouteInterface $route, array &$document): void
    {
        $requestHandler = $this->requestHandlerReflector->reflectRequestHandler($route->getRequestHandler());

        /** @var array<array-key, mixed> $operation */
        $operation = [];
        foreach (ReflectorHelper::getAncestry($requestHandler) as $member) {
            /** @var list<ReflectionAttribute<Operation>> $annotations */
            $annotations = $member->getAttributes(Operation::class);
            foreach ($annotations as $annotation) {
                $annotation = $annotation->newInstance();
                $operation = array_replace_recursive($operation, $annotation->value);
            }
        }

        $operation['operationId'] = $route->getName();
        $operation['tags'] = $route->getTags();
        $operation['summary'] = $route->getSummary();
        $operation['description'] = $route->getDescription();
        $operation['deprecated'] = $route->isDeprecated();

        $this->openApiOperationEnricherManager->enrichOperation($route, $requestHandler, $operation);

        $path = RouteSimplifier::simplifyRoute($route->getPath());
        foreach ($route->getMethods() as $method) {
            $document['paths'][$path][strtolower($method)] = $operation;
        }
    }
}
