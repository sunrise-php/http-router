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

namespace Sunrise\Http\Router\ResponseResolver;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Dictionary\AttributeName;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\Router;

/**
 * RouteResponseResolver
 *
 * @since 3.0.0
 */
final class RouteResponseResolver implements ResponseResolverInterface
{

    /**
     * @inheritDoc
     */
    public function resolveResponse(
        ServerRequestInterface $request,
        mixed $response,
        ReflectionFunction|ReflectionMethod $responder,
    ) : ?ResponseInterface {
        if (! $response instanceof Route) {
            return null;
        }

        $router = $request->getAttribute(AttributeName::ROUTER);
        if (! $router instanceof Router) {
            throw new LogicException();
        }

        return $router->runRoute($response, $request);
    }
}
