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

use Fig\Http\Message\RequestMethodInterface;

/**
 * RouteCollector
 */
class RouteCollector
{

    /**
     * @var RouteCollectionInterface
     */
    private RouteCollectionInterface $collection;

    /**
     * @var RouteCollectionFactoryInterface
     */
    private RouteCollectionFactoryInterface $collectionFactory;

    /**
     * @var RouteFactoryInterface
     */
    private RouteFactoryInterface $routeFactory;

    /**
     * @var ReferenceResolverInterface
     */
    private ReferenceResolverInterface $referenceResolver;

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
        ?ReferenceResolverInterface $referenceResolver = null,
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();
        $this->referenceResolver = $referenceResolver ?? new ReferenceResolver();

        $this->collection = $this->collectionFactory->createCollection();
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
     * Makes a new route with the given parameters
     *
     * @param string $name
     * @param string $path
     * @param list<string> $methods
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function route(
        string $name,
        string $path,
        array $methods,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        $route = $this->routeFactory->createRoute(
            $name,
            $path,
            $methods,
            $this->referenceResolver->resolveRequestHandler($requestHandler),
            [...$this->referenceResolver->resolveMiddlewares($middlewares)],
            $attributes,
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
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function head(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_HEAD],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to GET requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function get(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_GET],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to POST requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function post(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_POST],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to PUT requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function put(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_PUT],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to PATCH requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function patch(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_PATCH],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to DELETE requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function delete(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_DELETE],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to PURGE requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function purge(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_PURGE],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Route grouping logic
     *
     * @param callable $callback
     * @param list<mixed> $middlewares
     *
     * @return RouteCollectionInterface
     */
    public function group(callable $callback, array $middlewares = []): RouteCollectionInterface
    {
        $collector = new self(
            $this->collectionFactory,
            $this->routeFactory,
            $this->referenceResolver,
        );

        $callback($collector);

        $collector->collection->addPriorityMiddleware(
            ...$this->referenceResolver->resolveMiddlewares($middlewares)
        );

        $this->collection->add(...$collector->collection->all());

        return $collector->collection;
    }
}
