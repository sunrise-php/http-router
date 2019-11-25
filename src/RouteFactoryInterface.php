<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * RouteFactoryInterface
 */
interface RouteFactoryInterface
{

    /**
     * Creates a new route from the given parameters
     *
     * @param string $name
     * @param string $path
     * @param string[] $methods
     * @param RequestHandlerInterface $requestHandler
     * @param MiddlewareInterface[] $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function createRoute(
        string $name,
        string $path,
        array $methods,
        RequestHandlerInterface $requestHandler,
        array $middlewares,
        array $attributes
    ) : RouteInterface;
}
