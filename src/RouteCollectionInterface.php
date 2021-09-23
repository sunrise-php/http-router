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

/**
 * RouteCollectionInterface
 */
interface RouteCollectionInterface
{

    /**
     * Adds the given route(s) to the collection
     *
     * @param RouteInterface ...$routes
     *
     * @return void
     */
    public function add(RouteInterface ...$routes) : void;

    /**
     * Gets all routes from the collection
     *
     * @return RouteInterface[]
     */
    public function all() : array;

    /**
     * Sets the given host to all routes in the collection
     *
     * @param string $host
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function setHost(string $host) : RouteCollectionInterface;

    /**
     * Adds the given path prefix to all routes in the collection
     *
     * @param string $prefix
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function addPrefix(string $prefix) : RouteCollectionInterface;

    /**
     * Adds the given path suffix to all routes in the collection
     *
     * @param string $suffix
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function addSuffix(string $suffix) : RouteCollectionInterface;

    /**
     * Adds the given method(s) to all routes in the collection
     *
     * @param string ...$methods
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function addMethod(string ...$methods) : RouteCollectionInterface;

    /**
     * Appends (to top) the given middleware(s) to all routes in the collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function appendMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionInterface;

    /**
     * Prepends (to end) the given middleware(s) to all routes in the collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function prependMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionInterface;
}
