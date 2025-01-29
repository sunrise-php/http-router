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

use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\RequestQuery;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class RequestQueryOperationEnricher implements
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

        $requestQuerySchema = null;
        foreach ($requestHandler->getParameters() as $requestHandlerParameter) {
            if ($requestHandlerParameter->getAttributes(RequestQuery::class) !== []) {
                $requestQueryType = TypeFactory::fromPhpTypeReflection($requestHandlerParameter->getType());
                $requestQuerySchema = $this->openApiPhpTypeSchemaResolverManager
                    ->resolvePhpTypeSchema($requestQueryType, $requestHandlerParameter);
                break;
            }
        }

        if ($requestQuerySchema === null) {
            return;
        }

        $operation['parameters'][] = [
            'in' => 'query',
            'name' => 'Query',
            'schema' => $requestQuerySchema,
            'required' => true,
        ];
    }

    public function getWeight(): int
    {
        return 30;
    }
}
