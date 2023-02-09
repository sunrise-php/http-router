<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Psr\Http\Server\MiddlewareInterface;
use Countable;

/**
 * RouteCollectionInterface
 */
interface RouteCollectionInterface extends Countable
{

    /**
     * Gets all routes from the collection
     *
     * @return list<RouteInterface>
     */
    public function all(): array;

    /**
     * Gets a route by the given name
     *
     * @param string $name
     *
     * @return RouteInterface|null
     *
     * @since 2.10.0
     */
    public function get(string $name): ?RouteInterface;

    /**
     * Checks by the given name if a route exists in the collection
     *
     * @param string $name
     *
     * @return bool
     *
     * @since 2.10.0
     */
    public function has(string $name): bool;

    /**
     * Adds the given route(s) to the collection
     *
     * @param RouteInterface ...$routes
     *
     * @return RouteCollectionInterface
     */
    public function add(RouteInterface ...$routes): RouteCollectionInterface;

    /**
     * Sets the given host to all routes in the collection
     *
     * @param string $host
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function setHost(string $host): RouteCollectionInterface;

    /**
     * Sets the given consumed content type(s) to all routes in the collection
     *
     * @param string ...$contentTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function setConsumedContentTypes(string ...$contentTypes): RouteCollectionInterface;

    /**
     * Sets the given produced content type(s) to all routes in the collection
     *
     * @param string ...$contentTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function setProducedContentTypes(string ...$contentTypes): RouteCollectionInterface;

    /**
     * Sets the given attribute to all routes in the collection
     *
     * @param string $name
     * @param mixed $value
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function setAttribute(string $name, $value): RouteCollectionInterface;

    /**
     * Adds the given path prefix to all routes in the collection
     *
     * @param string $prefix
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function addPrefix(string $prefix): RouteCollectionInterface;

    /**
     * Adds the given path suffix to all routes in the collection
     *
     * @param string $suffix
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function addSuffix(string $suffix): RouteCollectionInterface;

    /**
     * Adds the given method(s) to all routes in the collection
     *
     * @param string ...$methods
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function addMethod(string ...$methods): RouteCollectionInterface;

    /**
     * Adds the given consumed content type(s) to all routes in the collection
     *
     * @param string ...$contentTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function addConsumedContentType(string ...$contentTypes): RouteCollectionInterface;

    /**
     * Adds the given produced content type(s) to all routes in the collection
     *
     * @param string ...$contentTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function addProducedContentType(string ...$contentTypes): RouteCollectionInterface;

    /**
     * Adds the given middleware(s) to all routes in the collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteCollectionInterface
     *
     * @since 2.9.0
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares): RouteCollectionInterface;

    /**
     * Adds the given priority middleware(s) to all routes in the collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function addPriorityMiddleware(MiddlewareInterface ...$middlewares): RouteCollectionInterface;

    /**
     * Adds the given tag(s) to all routes in the collection
     *
     * @param string ...$tags
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function addTag(string ...$tags): RouteCollectionInterface;
}
