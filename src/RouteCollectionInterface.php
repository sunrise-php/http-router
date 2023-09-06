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

namespace Sunrise\Http\Router;

use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Entity\MediaType;
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
     */
    public function add(RouteInterface ...$routes): RouteCollectionInterface;

    /**
     * Sets the given consumes media type(s) to all routes in the collection
     *
     * @param MediaType ...$mediaTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function setConsumesMediaTypes(MediaType ...$mediaTypes): RouteCollectionInterface;

    /**
     * Sets the given produces media type(s) to all routes in the collection
     *
     * @param MediaType ...$mediaTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function setProducesMediaTypes(MediaType ...$mediaTypes): RouteCollectionInterface;

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
     * Adds the given consumes media type(s) to all routes in the collection
     *
     * @param MediaType ...$mediaTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function addConsumesMediaType(MediaType ...$mediaTypes): RouteCollectionInterface;

    /**
     * Adds the given produces media type(s) to all routes in the collection
     *
     * @param MediaType ...$mediaTypes
     *
     * @return RouteCollectionInterface
     *
     * @since 3.0.0
     */
    public function addProducesMediaType(MediaType ...$mediaTypes): RouteCollectionInterface;

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
