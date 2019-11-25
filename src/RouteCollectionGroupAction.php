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
 * RouteCollectionGroupAction
 */
class RouteCollectionGroupAction implements RouteCollectionGroupActionInterface
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
     * {@inheritDoc}
     */
    public function addPrefix(string $prefix) : RouteCollectionGroupActionInterface
    {
        foreach ($this->collection as $route) {
            $route->addPrefix($prefix);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addSuffix(string $suffix) : RouteCollectionGroupActionInterface
    {
        foreach ($this->collection as $route) {
            $route->addSuffix($suffix);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addMethod(string ...$methods) : RouteCollectionGroupActionInterface
    {
        foreach ($this->collection as $route) {
            $route->addMethod(...$methods);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionGroupActionInterface
    {
        foreach ($this->collection as $route) {
            $route->addMiddleware(...$middlewares);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unshiftMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionGroupActionInterface
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
