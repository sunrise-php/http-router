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
 * RouteCollector
 */
class RouteCollector
{

    /**
     * The collector a route collection factory
     *
     * @var RouteCollectionFactoryInterface
     */
    private $collectionFactory;

    /**
     * The collector a route factory
     *
     * @var RouteFactoryInterface
     */
    private $routeFactory;

    /**
     * The collector a route collection
     *
     * @var RouteCollectionInterface
     */
    private $collection;

    /**
     * Constructor of the class
     *
     * @param null|RouteCollectionFactoryInterface $collectionFactory
     * @param null|RouteFactoryInterface $routeFactory
     */
    public function __construct(
        RouteCollectionFactoryInterface $collectionFactory = null,
        RouteFactoryInterface $routeFactory = null
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();

        $this->collection = $this->collectionFactory->createCollection();
    }

    /**
     * Gets the collector collection
     *
     * @return RouteCollectionInterface
     */
    public function getCollection() : RouteCollectionInterface
    {
        return $this->collection;
    }

    /**
     * Makes a new route from the given parameters
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
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        $route = $this->routeFactory->createRoute(
            $name,
            $path,
            $methods,
            $requestHandler,
            $middlewares,
            $attributes
        );

        $this->collection->add($route);

        return $route;
    }

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
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [Router::METHOD_HEAD],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

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
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [Router::METHOD_GET],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

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
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [Router::METHOD_POST],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

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
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [Router::METHOD_PUT],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

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
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [Router::METHOD_PATCH],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

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
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [Router::METHOD_DELETE],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

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
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [Router::METHOD_PURGE],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

    /**
     * Route grouping logic
     *
     * @param callable $callback
     *
     * @return RouteCollectorGroupAction
     */
    public function group(callable $callback) : RouteCollectorGroupAction
    {
        $collector = new self(
            $this->collectionFactory,
            $this->routeFactory
        );

        $callback($collector);

        $this->collection->add(...$collector->collection->all());

        return new RouteCollectorGroupAction($collector->collection);
    }
}
