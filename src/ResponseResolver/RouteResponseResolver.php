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
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\Router;

/**
 * @since 3.0.0
 */
final class RouteResponseResolver implements ResponseResolverInterface
{
    public function resolveResponse(
        mixed $response,
        ReflectionMethod|ReflectionFunction $responder,
        ServerRequestInterface $request,
    ): ?ResponseInterface {
        if (! $response instanceof Route) {
            return null;
        }

        $router = $request->getAttribute('@router');
        if (! $router instanceof Router) {
            throw new LogicException(
                'Something went wrong, the request must contain the @router attribute.'
            );
        }

        return $router->runRoute($response, $request);
    }
}
