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
 * RouteCollectionInterface
 */
interface RouteCollectionInterface
{

    /**
     * Gets the collection prefix
     *
     * @return null|string
     */
    public function getPrefix() : ?string;

    /**
     * Gets the collection middlewares
     *
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares() : array;

    /**
     * Gets the collection routes
     *
     * @return RouteInterface[]
     */
    public function getRoutes() : array;

    /**
     * Sets the given prefix to the collection
     *
     * This method MUST set the given prefix to the collection AS IS.
     *
     * @param string $prefix
     *
     * @return RouteCollectionInterface
     */
    public function setPrefix(string $prefix) : RouteCollectionInterface;

    /**
     * Adds the given middleware(s) to the collection
     *
     * This method MUST add the given middlewares to the collection AS IS.
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteCollectionInterface
     */
    public function addMiddlewares(MiddlewareInterface ...$middlewares) : RouteCollectionInterface;

    /**
     * Adds the given route(s) to the collection
     *
     * This method MUST add the given routes to the collection AS IS.
     *
     * @param RouteInterface ...$routes
     *
     * @return RouteCollectionInterface
     */
    public function addRoutes(RouteInterface ...$routes) : RouteCollectionInterface;

    /**
     * Makes a new route from the given parameters
     *
     * This method MUST make a route using the collection prefix and middlewares.
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
    public function route(
        string $name,
        string $path,
        array $methods,
        RequestHandlerInterface $requestHandler,
        array $middlewares,
        array $attributes
    ) : RouteInterface;

    /**
     * Makes a new route that will respond to HEAD requests
     *
     * @param string $name
     * @param string $path
     * @param RequestHandlerInterface $requestHandler
     * @param MiddlewareInterface[] $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function head(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares,
        array $attributes
    ) : RouteInterface;

    /**
     * Makes a new route that will respond to GET requests
     *
     * @param string $name
     * @param string $path
     * @param RequestHandlerInterface $requestHandler
     * @param MiddlewareInterface[] $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function get(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares,
        array $attributes
    ) : RouteInterface;

    /**
     * Makes a new route that will respond to POST requests
     *
     * @param string $name
     * @param string $path
     * @param RequestHandlerInterface $requestHandler
     * @param MiddlewareInterface[] $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function post(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares,
        array $attributes
    ) : RouteInterface;

    /**
     * Makes a new route that will respond to PUT requests
     *
     * @param string $name
     * @param string $path
     * @param RequestHandlerInterface $requestHandler
     * @param MiddlewareInterface[] $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function put(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares,
        array $attributes
    ) : RouteInterface;

    /**
     * Makes a new route that will respond to PATCH requests
     *
     * @param string $name
     * @param string $path
     * @param RequestHandlerInterface $requestHandler
     * @param MiddlewareInterface[] $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function patch(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares,
        array $attributes
    ) : RouteInterface;

    /**
     * Makes a new route that will respond to DELETE requests
     *
     * @param string $name
     * @param string $path
     * @param RequestHandlerInterface $requestHandler
     * @param MiddlewareInterface[] $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function delete(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares,
        array $attributes
    ) : RouteInterface;

    /**
     * Makes a new route that will respond to PURGE requests
     *
     * @param string $name
     * @param string $path
     * @param RequestHandlerInterface $requestHandler
     * @param MiddlewareInterface[] $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     */
    public function purge(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares,
        array $attributes
    ) : RouteInterface;

    /**
     * Route grouping logic
     *
     * @param string $prefix
     * @param callable $callback
     *
     * @return void
     */
    public function group(string $prefix, callable $callback) : void;
}
