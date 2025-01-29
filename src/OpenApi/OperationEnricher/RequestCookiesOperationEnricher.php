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
final class RequestCookiesOperationEnricher implements
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

        foreach ($requestHandler->getParameters() as $requestHandlerParameter) {
            /** @var list<ReflectionAttribute<RequestCookie>> $annotations */
            $annotations = $requestHandlerParameter->getAttributes(RequestCookie::class);
            if (isset($annotations[0])) {
                $requestCookie = $annotations[0]->newInstance();
                $requestCookieType = TypeFactory::fromPhpTypeReflection($requestHandlerParameter->getType());
                $requestCookieSchema = $this->openApiPhpTypeSchemaResolverManager
                    ->resolvePhpTypeSchema($requestCookieType, $requestHandlerParameter);

                $operation['parameters'][] = [
                    'in' => 'cookie',
                    'name' => $requestCookie->name,
                    'schema' => $requestCookieSchema,
                    'required' => !$requestHandlerParameter->isDefaultValueAvailable(),
                ];
            }
        }
    }

    public function getWeight(): int
    {
        return 20;
    }
}
