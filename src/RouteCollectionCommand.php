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
 * RouteCollectionCommand
 */
class RouteCollectionCommand
{

    /**
     * Route collection
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
     * Adds the given prefix to all routes in the collection
     *
     * @param string $prefix
     *
     * @return self
     */
    public function addPrefix(string $prefix) : self
    {
        foreach ($this->collection as $route) {
            $route->addPrefix($prefix);
        }

        return $this;
    }

    /**
     * Adds the given suffix to all routes in the collection
     *
     * @param string $suffix
     *
     * @return self
     */
    public function addSuffix(string $suffix) : self
    {
        foreach ($this->collection as $route) {
            $route->addSuffix($suffix);
        }

        return $this;
    }

    /**
     * Adds the given method(s) to all routes in the collection
     *
     * @param string ...$methods
     *
     * @return self
     */
    public function addMethod(string ...$methods) : self
    {
        foreach ($this->collection as $route) {
            $route->addMethod(...$methods);
        }

        return $this;
    }

    /**
     * Adds the given middleware(s) to all routes in the collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return self
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : self
    {
        foreach ($this->collection as $route) {
            $route->addMiddleware(...$middlewares);
        }

        return $this;
    }

    /**
     * Adds the given middleware(s) to the beginning of all routes in the collection
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return self
     */
    public function unshiftMiddleware(MiddlewareInterface ...$middlewares) : self
    {
        foreach ($this->collection as $route) {
            $route->setMiddlewares(...array_merge(
                $middlewares,
                $route->getMiddlewares()
            ));
        }

        return $this;
    }
}
