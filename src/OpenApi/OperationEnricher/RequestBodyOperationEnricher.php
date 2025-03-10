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

namespace Sunrise\Http\Router\OpenApi\OperationEnricher;

use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class RequestBodyOperationEnricher implements
    OpenApiOperationEnricherInterface,
    OpenApiPhpTypeSchemaResolverManagerAwareInterface
{
    private readonly OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager;

    public function setOpenApiPhpTypeSchemaResolverManager(
        OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager,
    ): void {
        $this->openApiPhpTypeSchemaResolverManager = $openApiPhpTypeSchemaResolverManager;
    }

    /**
     * @inheritDoc
     */
    public function enrichOperation(
        RouteInterface $route,
        ReflectionClass|ReflectionMethod $requestHandler,
        array &$operation,
    ): void {
        if (! $requestHandler instanceof ReflectionMethod) {
            return;
        }

        $requestBodySchema = null;
        foreach ($requestHandler->getParameters() as $requestHandlerParameter) {
            $requestBodyType = TypeFactory::fromPhpTypeReflection($requestHandlerParameter->getType());
            if (
                $requestHandlerParameter->getAttributes(RequestBody::class) !== [] ||
                $requestBodyType->is(StreamInterface::class)
            ) {
                $requestBodySchema = $this->openApiPhpTypeSchemaResolverManager
                    ->resolvePhpTypeSchema($requestBodyType, $requestHandlerParameter);
                break;
            }
        }

        if ($requestBodySchema === null) {
            return;
        }

        $operation['requestBody'] = [];

        foreach ($route->getConsumedMediaTypes() as $consumedMediaType) {
            $operation['requestBody']['content'][$consumedMediaType->getIdentifier()] = [
                'schema' => $requestBodySchema,
            ];
        }

        $operation['requestBody']['required'] = true;
    }

    public function getWeight(): int
    {
        return 0;
    }
}
