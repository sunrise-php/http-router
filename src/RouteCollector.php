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
use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\Exception\UnresolvableReferenceException;

/**
 * RouteCollector
 */
class RouteCollector
{

    /**
     * Route collection factory of the collector
     *
     * @var RouteCollectionFactoryInterface
     */
    private $collectionFactory;

    /**
     * Route factory of the collector
     *
     * @var RouteFactoryInterface
     */
    private $routeFactory;

    /**
     * Reference resolver of the collector
     *
     * @var ReferenceResolverInterface
     */
    private $referenceResolver;

    /**
     * Route collection of the collector
     *
     * @var RouteCollectionInterface
     */
    private $collection;

    /**
     * Constructor of the class
     *
     * @param RouteCollectionFactoryInterface|null $collectionFactory
     * @param RouteFactoryInterface|null $routeFactory
     * @param ReferenceResolverInterface|null $referenceResolver
     */
    public function __construct(
        ?RouteCollectionFactoryInterface $collectionFactory = null,
        ?RouteFactoryInterface $routeFactory = null,
        ?ReferenceResolverInterface $referenceResolver = null
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();
        $this->referenceResolver = $referenceResolver ?? new ReferenceResolver();

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
     * Gets the collector container
     *
     * @return ContainerInterface|null
     *
     * @since 2.9.0
     */
    public function getContainer() : ?ContainerInterface
    {
        return $this->referenceResolver->getContainer();
    }

    /**
     * Sets the given container to the collector
     *
     * @param ContainerInterface|null $container
     *
     * @return void
     *
     * @since 2.9.0
     */
    public function setContainer(?ContainerInterface $container) : void
    {
        $this->referenceResolver->setContainer($container);
    }

    /**
     * Makes a new route from the given parameters
     *
     * @param string $name
     * @param string $path
     * @param string[] $methods
     * @param mixed $requestHandler
     * @param array $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     *
     * @throws UnresolvableReferenceException
     *         If the given request handler or one of the given middlewares cannot be resolved.
     */
    public function route(
        string $name,
        string $path,
        array $methods,
        $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        foreach ($middlewares as &$middleware) {
            $middleware = $this->referenceResolver->toMiddleware($middleware);
        }

        $route = $this->routeFactory->createRoute(
            $name,
            $path,
            $methods,
            $this->referenceResolver->toRequestHandler($requestHandler),
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
     * @param mixed $requestHandler
     * @param array $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     *
     * @throws UnresolvableReferenceException
     *         If the given request handler or one of the given middlewares cannot be resolved.
     */
    public function head(
        string $name,
        string $path,
        $requestHandler,
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
     * @param mixed $requestHandler
     * @param array $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     *
     * @throws UnresolvableReferenceException
     *         If the given request handler or one of the given middlewares cannot be resolved.
     */
    public function get(
        string $name,
        string $path,
        $requestHandler,
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
     * @param mixed $requestHandler
     * @param array $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     *
     * @throws UnresolvableReferenceException
     *         If the given request handler or one of the given middlewares cannot be resolved.
     */
    public function post(
        string $name,
        string $path,
        $requestHandler,
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
     * @param mixed $requestHandler
     * @param array $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     *
     * @throws UnresolvableReferenceException
     *         If the given request handler or one of the given middlewares cannot be resolved.
     */
    public function put(
        string $name,
        string $path,
        $requestHandler,
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
     * @param mixed $requestHandler
     * @param array $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     *
     * @throws UnresolvableReferenceException
     *         If the given request handler or one of the given middlewares cannot be resolved.
     */
    public function patch(
        string $name,
        string $path,
        $requestHandler,
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
     * @param mixed $requestHandler
     * @param array $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     *
     * @throws UnresolvableReferenceException
     *         If the given request handler or one of the given middlewares cannot be resolved.
     */
    public function delete(
        string $name,
        string $path,
        $requestHandler,
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
     * @param mixed $requestHandler
     * @param array $middlewares
     * @param array $attributes
     *
     * @return RouteInterface
     *
     * @throws UnresolvableReferenceException
     *         If the given request handler or one of the given middlewares cannot be resolved.
     */
    public function purge(
        string $name,
        string $path,
        $requestHandler,
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
     * @param array $middlewares
     *
     * @return RouteCollectionInterface
     *
     * @throws UnresolvableReferenceException
     *         If one of the given middlewares cannot be resolved.
     */
    public function group(callable $callback, array $middlewares = []) : RouteCollectionInterface
    {
        foreach ($middlewares as &$middleware) {
            $middleware = $this->referenceResolver->toMiddleware($middleware);
        }

        $collector = new self(
            $this->collectionFactory,
            $this->routeFactory,
            $this->referenceResolver
        );

        $callback($collector);

        $collector->collection->prependMiddleware(...$middlewares);

        $this->collection->add(...$collector->collection->all());

        return $collector->collection;
    }
}
