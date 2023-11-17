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

use Countable;
use Generator;
use IteratorAggregate;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Entity\MediaType;

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
     * @return Generator<int, RouteInterface>
     */
    public function all(): Generator;

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
     * @return RouteInterface|null
     *
     * @since 2.10.0
     */
    public function get(string $name): ?RouteInterface;

    /**
     * Adds the given route(s) to the collection
     *
     * @param RouteInterface ...$routes
     *
     * @return static
     */
    public function add(RouteInterface ...$routes): static;

    /**
     * Adds the given path prefix to all routes in the collection
     *
     * @param string $prefix
     *
     * @return static
     *
     * @since 2.9.0
     */
    public function addPrefix(string $prefix): static;

    /**
     * Adds the given path suffix to all routes in the collection
     *
     * @param string $suffix
     *
     * @return static
     *
     * @since 2.9.0
     */
    public function addSuffix(string $suffix): static;

    /**
     * Adds the given method(s) to all routes in the collection
     *
     * @param string ...$methods
     *
     * @return static
     *
     * @since 2.9.0
     */
    public function addMethod(string ...$methods): static;

    /**
     * Adds the given consumes media type(s) to all routes in the collection
     *
     * @param MediaType ...$mediaTypes
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function addConsumesMediaType(MediaType ...$mediaTypes): static;

    /**
     * Adds the given produces media type(s) to all routes in the collection
     *
     * @param MediaType ...$mediaTypes
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function addProducesMediaType(MediaType ...$mediaTypes): static;

    /**
     * Adds the given priority middleware(s) to all routes in the collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function addPriorityMiddleware(MiddlewareInterface ...$middlewares): static;

    /**
     * Adds the given middleware(s) to all routes in the collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return static
     *
     * @since 2.9.0
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares): static;

    /**
     * Adds the given tag(s) to all routes in the collection
     *
     * @param string ...$tags
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function addTag(string ...$tags): static;

    /**
     * Sets the given deprecation sign to all routes in the collection
     *
     * @param bool $isDeprecated
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function setDeprecation(bool $isDeprecated): static;
}
