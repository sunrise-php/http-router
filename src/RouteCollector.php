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
use Closure;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\UnresolvableObjectException;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;

/**
 * Import functions
 */
use function is_string;
use function is_subclass_of;
use function sprintf;

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
     * Route collection of the collector
     *
     * @var RouteCollectionInterface
     */
    private $collection;

    /**
     * The collector container
     *
     * @var null|ContainerInterface
     */
    private $container = null;

    /**
     * Constructor of the class
     *
     * @param null|RouteCollectionFactoryInterface $collectionFactory
     * @param null|RouteFactoryInterface $routeFactory
     */
    public function __construct(
        ?RouteCollectionFactoryInterface $collectionFactory = null,
        ?RouteFactoryInterface $routeFactory = null
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
     * Gets the collector container
     *
     * @return null|ContainerInterface
     *
     * @since 2.9.0
     */
    public function getContainer() : ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Sets the given container to the collector
     *
     * @param null|ContainerInterface $container
     *
     * @return void
     *
     * @since 2.9.0
     */
    public function setContainer(?ContainerInterface $container) : void
    {
        $this->container = $container;
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
            $this->resolveRequestHandler($name, $requestHandler),
            $this->resolveMiddlewares($name, $middlewares),
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
     *
     * @return RouteCollectionInterface
     */
    public function group(callable $callback) : RouteCollectionInterface
    {
        $collector = new self(
            $this->collectionFactory,
            $this->routeFactory
        );

        $collector->setContainer($this->container);

        $callback($collector);

        $this->collection->add(...$collector->collection->all());

        return $collector->collection;
    }

    /**
     * Tries to resolve the given request handler
     *
     * @param string $routeName
     * @param mixed $requestHandler
     *
     * @return RequestHandlerInterface
     *
     * @throws UnresolvableObjectException
     *
     * @since 2.9.0
     *
     * @todo Maybe move to a new abstract layer and think about deeper integration into the router...
     */
    private function resolveRequestHandler(string $routeName, $requestHandler) : RequestHandlerInterface
    {
        if ($requestHandler instanceof RequestHandlerInterface) {
            return $requestHandler;
        }

        if ($requestHandler instanceof Closure) {
            return new CallableRequestHandler($requestHandler);
        }

        if (!is_string($requestHandler) || !is_subclass_of($requestHandler, RequestHandlerInterface::class)) {
            throw new UnresolvableObjectException(sprintf('Route %s refers to invalid request handler.', $routeName));
        }

        if ($this->container && $this->container->has($requestHandler)) {
            return $this->container->get($requestHandler);
        }

        return new $requestHandler;
    }

    /**
     * Tries to resolve the given middleware
     *
     * @param string $routeName
     * @param mixed $middleware
     *
     * @return MiddlewareInterface
     *
     * @throws UnresolvableObjectException
     *
     * @since 2.9.0
     *
     * @todo Maybe move to a new abstract layer and think about deeper integration into the router...
     */
    private function resolveMiddleware(string $routeName, $middleware) : MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        if ($middleware instanceof Closure) {
            return new CallableMiddleware($middleware);
        }

        if (!is_string($middleware) || !is_subclass_of($middleware, MiddlewareInterface::class)) {
            throw new UnresolvableObjectException(sprintf('Route %s refers to invalid middleware.', $routeName));
        }

        if ($this->container && $this->container->has($middleware)) {
            return $this->container->get($middleware);
        }

        return new $middleware;
    }

    /**
     * Tries to resolve the given middlewares
     *
     * @param string $routeName
     * @param array $middlewares
     *
     * @return MiddlewareInterface[]
     *
     * @throws UnresolvableObjectException
     *
     * @since 2.9.0
     *
     * @todo Maybe move to a new abstract layer and think about deeper integration into the router...
     */
    private function resolveMiddlewares(string $routeName, array $middlewares) : array
    {
        if (empty($middlewares)) {
            return [];
        }

        foreach ($middlewares as &$middleware) {
            $middleware = $this->resolveMiddleware($routeName, $middleware);
        }

        return $middlewares;
    }
}
