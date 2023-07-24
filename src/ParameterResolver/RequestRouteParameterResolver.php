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
use Sunrise\Http\Router\RouteInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * RequestRouteParameterResolver
 *
 * @since 3.0.0
 */
final class RequestRouteParameterResolver implements ParameterResolverInterface
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

        if (! $type instanceof ReflectionNamedType) {
            return false;
        }

        if (! ($type->getName() === RouteInterface::class)) {
            return false;
        }

        /** @var RouteInterface|null $route */
        $route = $request->getAttribute('@route');

        return isset($route) || $type->allowsNull();
    }

    /**
     * @inheritDoc
     */
    public function resolveParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): mixed
    {
        return $request?->getAttribute('@route');
    }
}
