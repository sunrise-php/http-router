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
use Sunrise\Http\Router\Exception\RouteAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Countable;
use Iterator;
use IteratorAggregate;

/**
 * RouteCollectionInterface
 *
 * @extends IteratorAggregate<int, RouteInterface>
 */
interface RouteCollectionInterface extends Countable, IteratorAggregate
{

    /**
     * Gets all routes from the collection
     *
     * @return Iterator<int, RouteInterface>
     */
    public function all(): Iterator;

    /**
     * Gets all routes from the collection by the given host
     *
     * This method should first return all routes that are served on the given host,
     * and then return all routes that are not bound to any host.
     *
     * @param string|null $host
     *
     * @return Iterator<int, RouteInterface>
     *
     * @since 3.0.0
     */
    public function allOnHost(?string $host): Iterator;

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
     * Gets a route by the given name
     *
     * @param string $name
     *
     * @return RouteInterface
     *
     * @throws RouteNotFoundException
     *         If the collection doesn't contain a route with the name.
     *
     * @since 2.10.0
     */
    public function get(string $name): RouteInterface;

    /**
     * Adds the given route(s) to the collection
     *
     * @param RouteInterface ...$routes
     *
     * @return RouteCollectionInterface
     *
     * @throws RouteAlreadyExistsException
     *         If the collection already contains a route with the name.
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
     * Sets the given consumed media type(s) to all routes in the collection
     *
     * @param string ...$mediaTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function setConsumedMediaTypes(string ...$mediaTypes): RouteCollectionInterface;

    /**
     * Sets the given produced media type(s) to all routes in the collection
     *
     * @param string ...$mediaTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function setProducedMediaTypes(string ...$mediaTypes): RouteCollectionInterface;

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
     * Adds the given consumed media type(s) to all routes in the collection
     *
     * @param string ...$mediaTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function addConsumedMediaType(string ...$mediaTypes): RouteCollectionInterface;

    /**
     * Adds the given produced media type(s) to all routes in the collection
     *
     * @param string ...$mediaTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function addProducedMediaType(string ...$mediaTypes): RouteCollectionInterface;

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
