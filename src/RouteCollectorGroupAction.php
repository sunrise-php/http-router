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
 * Import functions
 */
use function array_merge;

/**
 * RouteCollectorGroupAction
 */
class RouteCollectorGroupAction
{

    /**
     * Route collection for group activities
     *
     * @var RouteCollectionInterface
     */
    private $collection;

    /**
     * Constructor of the class
     *
     * @param RouteCollectionInterface $collection
     */
    public function __construct(RouteCollectionInterface $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Adds the given path prefix to all routes in the collection
     *
     * @param string $prefix
     *
     * @return RouteCollectionGroupActionInterface
     */
    public function addPrefix(string $prefix) : self
    {
        foreach ($this->collection->all() as $route) {
            $route->addPrefix($prefix);
        }

        return $this;
    }

    /**
     * Adds the given path suffix to all routes in the collection
     *
     * @param string $suffix
     *
     * @return RouteCollectionGroupActionInterface
     */
    public function addSuffix(string $suffix) : self
    {
        foreach ($this->collection->all() as $route) {
            $route->addSuffix($suffix);
        }

        return $this;
    }

    /**
     * Adds the given method(s) to all routes in the collection
     *
     * @param string ...$methods
     *
     * @return RouteCollectionGroupActionInterface
     */
    public function addMethod(string ...$methods) : self
    {
        foreach ($this->collection->all() as $route) {
            $route->addMethod(...$methods);
        }

        return $this;
    }

    /**
     * Adds the given middleware(s) to all routes in the collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteCollectionGroupActionInterface
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : self
    {
        foreach ($this->collection->all() as $route) {
            $route->addMiddleware(...$middlewares);
        }

        return $this;
    }

    /**
     * Adds the given middleware(s) to the beginning of all routes in the collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return RouteCollectionGroupActionInterface
     */
    public function unshiftMiddleware(MiddlewareInterface ...$middlewares) : self
    {
        foreach ($this->collection->all() as $route) {
            $route->setMiddlewares(...array_merge(
                $middlewares,
                $route->getMiddlewares()
            ));
        }

        return $this;
    }
}
