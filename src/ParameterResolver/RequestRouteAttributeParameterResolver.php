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

namespace Sunrise\Http\Router\ParameterResolver;

use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Annotation\RequestRouteAttribute;
use Sunrise\Http\Router\RouteInterface;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;

final class RequestRouteAttributeParameterResolver implements ParameterResolverInterface
{

    /**
     * @inheritDoc
     */
    public function supportsParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): bool
    {
        if ($request === null) {
            return false;
        }

        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || ! $type->isBuiltin()) {
            return false;
        }

        if ($parameter->getAttributes(RequestRouteAttribute::class) === []) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function resolveParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): mixed
    {
        /** @var non-empty-list<ReflectionAttribute<RequestRouteAttribute>> $annotations */
        $annotations = $parameter->getAttributes(RequestRouteAttribute::class);

        $key = $annotations[0]->newInstance()->name ?? $parameter->getName();

        /** @var RouteInterface $route */
        $route = $request->getAttribute('@route');

        /** @var mixed $value */
        $value = $route->getAttributes()[$key] ?? null;

        return $value;
    }
}
