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
use Psr\Container\ContainerInterface;

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
    private RouteCollectionFactoryInterface $collectionFactory;

    /**
     * Route factory of the collector
     *
     * @var RouteFactoryInterface
     */
    private RouteFactoryInterface $routeFactory;

    /**
     * Reference resolver of the collector
     *
     * @var ReferenceResolverInterface
     */
    private ReferenceResolverInterface $referenceResolver;

    /**
     * Route collection of the collector
     *
     * @var RouteCollectionInterface
     */
    private RouteCollectionInterface $collection;

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
     * Sets the given container to the collector
     *
     * @param ContainerInterface|null $container
     *
     * @return void
     *
     * @since 2.9.0
     */
    public function setContainer(?ContainerInterface $container): void
    {
        $this->referenceResolver->setContainer($container);
    }

    /**
     * Adds the given parameter resolver(s) to the collector
     *
     * @param ParameterResolverInterface ...$resolvers
     *
     * @return void
     *
     * @since 3.0.0
     */
    public function addParameterResolver(ParameterResolverInterface ...$resolvers): void
    {
        $this->referenceResolver->addParameterResolver(...$resolvers);
    }

    /**
     * Adds the given response resolver(s) to the collector
     *
     * @param ResponseResolverInterface ...$resolvers
     *
     * @return void
     *
     * @since 3.0.0
     */
    public function addResponseResolver(ResponseResolverInterface ...$resolvers): void
    {
        $this->referenceResolver->addResponseResolver(...$resolvers);
    }

    /**
     * Gets the collector's route collection
     *
     * @return RouteCollectionInterface
     */
    public function getCollection(): RouteCollectionInterface
    {
        return $this->collection;
    }

    /**
     * Makes a new route from the given parameters
     *
     * @param string $name
     * @param string $path
     * @param list<string> $methods
     * @param mixed $requestHandler
     * @param array<array-key, mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function route(
        string $name,
        string $path,
        array $methods,
        $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        $route = $this->routeFactory->createRoute(
            $name,
            $path,
            $methods,
            $this->referenceResolver->resolveRequestHandler($requestHandler),
            $this->referenceResolver->resolveMiddlewares($middlewares),
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
     * @param array<array-key, mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
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
     * @param array<array-key, mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
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
     * @param array<array-key, mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
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
     * @param array<array-key, mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
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
     * @param array<array-key, mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
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
     * @param array<array-key, mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
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
     * @param array<array-key, mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
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
     * @param array<array-key, mixed> $middlewares
     *
     * @return RouteCollectionInterface
     */
    public function group(callable $callback, array $middlewares = []): RouteCollectionInterface
    {
        $collector = new self(
            $this->collectionFactory,
            $this->routeFactory,
            $this->referenceResolver
        );

        $callback($collector);

        $collector->collection->prependMiddleware(
            ...$this->referenceResolver->resolveMiddlewares($middlewares)
        );

        $this->collection->add(
            ...$collector->collection->all()
        );

        return $collector->collection;
    }
}
