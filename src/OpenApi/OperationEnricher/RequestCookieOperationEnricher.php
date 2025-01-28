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

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\OpenApi\OpenApiOperationEnricherInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
final class RequestCookieOperationEnricher implements
    OpenApiOperationEnricherInterface,
    OpenApiPhpTypeSchemaResolverManagerAwareInterface
{
    private readonly OpenApiPhpTypeSchemaResolverManagerInterface $phpTypeSchemaResolverManager;

    public function setOpenApiPhpTypeSchemaResolverManager(
        OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager,
    ): void {
        $this->phpTypeSchemaResolverManager = $openApiPhpTypeSchemaResolverManager;
    }

    /**
     * @inheritDoc
     */
    public function enrichOperation(
        RouteInterface $route,
        ReflectionMethod|ReflectionClass $requestHandler,
        array &$operation,
    ): void {
        if (! $requestHandler instanceof ReflectionMethod) {
            return;
        }

        foreach ($requestHandler->getParameters() as $requestHandlerParameter) {
            /** @var list<ReflectionAttribute<RequestCookie>> $annotations */
            $annotations = $requestHandlerParameter->getAttributes(RequestCookie::class);
            if (!isset($annotations[0])) {
                continue;
            }

            $annotation = $annotations[0]->newInstance();

            $parameterType = TypeFactory::fromPhpTypeReflection($requestHandlerParameter->getType());
            $parameterSchema = $this->phpTypeSchemaResolverManager
                ->resolvePhpTypeSchema($parameterType, $requestHandlerParameter);

            $parameter['in'] = 'cookie';
            $parameter['name'] = $annotation->name;
            $parameter['schema'] = $parameterSchema;

            if (!$requestHandlerParameter->isDefaultValueAvailable()) {
                $parameter['required'] = true;
            }

            $operation['parameters'][] = $parameter;
        }
    }

    public function getWeight(): int
    {
        return 1;
    }
}
