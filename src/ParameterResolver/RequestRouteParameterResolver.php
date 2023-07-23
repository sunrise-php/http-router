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
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\RouteInterface;

/**
 * RequestRouteParameterResolver
 *
 * @since 3.0.0
 */
final class RequestRouteParameterResolver implements ParameterResolverInterface
{

    /**
     * {@inheritdoc}
     */
    public function supportsParameter(ReflectionParameter $parameter, $request): bool
    {
        if (!($request instanceof ServerRequestInterface)) {
            return false;
        }

        if (!($parameter->getType() instanceof ReflectionNamedType)) {
            return false;
        }

        if (!($parameter->getType()->getName() === RouteInterface::class)) {
            return false;
        }

        if (!($request->getAttribute(RouteInterface::ATTR_ROUTE) instanceof RouteInterface)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveParameter(ReflectionParameter $parameter, $request)
    {
        /** @var ServerRequestInterface $request */

        return $request->getAttribute(RouteInterface::ATTR_ROUTE);
    }
}
