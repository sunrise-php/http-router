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
use Fig\Http\Message\RequestMethodInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Event\RouteEvent;
use Sunrise\Http\Router\Exception\Http\HttpMethodNotAllowedException;
use Sunrise\Http\Router\Exception\Http\HttpNotAcceptableException;
use Sunrise\Http\Router\Exception\Http\HttpNotFoundException;
use Sunrise\Http\Router\Exception\Http\HttpUnsupportedMediaTypeException;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Sunrise\Http\Router\RequestHandler\UnsafeCallableRequestHandler;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_build;
use function Sunrise\Http\Router\path_match;

/**
 * Router
 */
class Router implements RequestHandlerInterface, RequestMethodInterface
{

    /**
     * Global patterns
     *
     * @var array<string, string>
     *
     * @since 2.9.0
     */
    public static array $patterns = [
        '@slug' => '[0-9a-z-]+',
        '@uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
    ];

    /**
     * The router's host table
     *
     * @var HostTable
     */
    private HostTable $hosts;

    /**
     * The router's routes
     *
     * @var RouteCollectionInterface
     */
    private RouteCollectionInterface $routes;

    /**
     * The router's middlewares
     *
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * The router's event dispatcher
     *
     * @var EventDispatcherInterface|null
     */
    private ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * The router's matched route
     *
     * @var RouteInterface|null
     */
    private ?RouteInterface $matchedRoute = null;

    /**
     * Constructor of the class
     *
     * @param HostTable|null $hosts
     * @param RouteCollectionInterface|null $routes
     *
     * @since 3.0.0
     */
    public function __construct(
        ?HostTable $hosts = null,
        ?RouteCollectionInterface $routes = null
    ) {
        $this->hosts = $hosts ?? new HostTable();
        $this->routes = $routes ?? new RouteCollection();
    }

    /**
     * Adds the given patterns to the router
     *
     * @param array<string, string> $patterns
     *
     * @return void
     *
     * @since 2.11.0
     */
    public function addPatterns(array $patterns): void
    {
        foreach ($patterns as $alias => $pattern) {
            self::$patterns[$alias] = $pattern;
        }
    }

    /**
     * Gets the router's host table
     *
     * @return HostTable
     *
     * @since 2.6.0
     */
    public function getHosts(): HostTable
    {
        return $this->hosts;
    }

    /**
     * Gets the router's route collection
     *
     * @return RouteCollectionInterface
     */
    public function getRoutes(): RouteCollectionInterface
    {
        return $this->routes;
    }

    /**
     * Adds the given middleware(s) to the router
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return void
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }
    }

    /**
     * Gets the router's middlewares
     *
     * @return list<MiddlewareInterface>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Gets the router's matched route
     *
     * @return RouteInterface|null
     */
    public function getMatchedRoute(): ?RouteInterface
    {
        return $this->matchedRoute;
    }

    /**
     * Sets the given event dispatcher to the router
     *
     * @param EventDispatcherInterface|null $eventDispatcher
     *
     * @return void
     *
     * @since 2.13.0
     */
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Gets the router's event dispatcher
     *
     * @return EventDispatcherInterface|null
     *
     * @since 2.13.0
     */
    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * Generates a URI for the given named route
     *
     * @param string $name
     * @param array<string, string> $attributes
     * @param bool $strict
     *
     * @return string
     */
    public function generateUri(string $name, array $attributes = [], bool $strict = false): string
    {
        $route = $this->routes->get($name);

        $attributes += $route->getAttributes();

        return path_build($route->getPath(), $attributes, $strict);
    }

    /**
     * Looks for a route that matches the given request
     *
     * @param ServerRequestInterface $request
     *
     * @return RouteInterface
     *
     * @throws HttpNotFoundException
     *         If the request URI cannot be matched against any route.
     *
     * @throws HttpMethodNotAllowedException
     *         If the request method isn't allowed.
     *
     * @throws HttpUnsupportedMediaTypeException
     *
     * @throws HttpNotAcceptableException
     */
    public function match(ServerRequestInterface $request): RouteInterface
    {
        $requestUri = $request->getUri();
        $requestHost = $requestUri->getHost();
        $requestPath = $requestUri->getPath();
        $requestMethod = $request->getMethod();
        $allowedMethods = [];

        $routes = $this->routes->allByHost($this->hosts->resolve($requestHost));

        $request = new ServerRequest($request);

        foreach ($routes as $route) {
            // https://github.com/sunrise-php/http-router/issues/50
            // https://tools.ietf.org/html/rfc7231#section-6.5.5
            if (!path_match($route->getPath(), $requestPath, $attributes)) {
                continue;
            }

            $routeMethods = [];
            foreach ($route->getMethods() as $routeMethod) {
                $routeMethods[$routeMethod] = true;
                $allowedMethods[$routeMethod] = $routeMethod;
            }

            if (!isset($routeMethods[$requestMethod])) {
                continue;
            }

            $consumedMediaTypes = $route->getConsumedMediaTypes();
            if (!empty($consumedMediaTypes) && !$request->clientProducesMediaType($consumedMediaTypes)) {
                throw new HttpUnsupportedMediaTypeException($consumedMediaTypes);
            }

            $producedMediaTypes = $route->getProducedMediaTypes();
            if (!empty($producedMediaTypes) && !$request->clientConsumesMediaType($producedMediaTypes)) {
                throw new HttpNotAcceptableException($producedMediaTypes);
            }

            /** @var array<string, string> $attributes */

            return $route->withAddedAttributes($attributes);
        }

        if (!empty($allowedMethods)) {
            throw new HttpMethodNotAllowedException($allowedMethods);
        }

        throw new HttpNotFoundException('Page Not Found');
    }

    /**
     * Runs the router
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     *
     * @since 2.8.0
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        // lazy resolving of the given request...
        $routing = new UnsafeCallableRequestHandler(
            function (ServerRequestInterface $request): ResponseInterface {
                $this->matchedRoute = $this->match($request);

                if (isset($this->eventDispatcher)) {
                    $this->eventDispatcher->dispatch(
                        new RouteEvent($this->matchedRoute, $request)
                    );
                }

                return $this->matchedRoute->handle($request);
            }
        );

        if (empty($this->middlewares)) {
            return $routing->handle($request);
        }

        $handler = new QueueableRequestHandler($routing);
        $handler->add(...$this->middlewares);

        return $handler->handle($request);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->matchedRoute = $this->match($request);

        if (isset($this->eventDispatcher)) {
            $this->eventDispatcher->dispatch(
                new RouteEvent($this->matchedRoute, $request)
            );
        }

        if (empty($this->middlewares)) {
            return $this->matchedRoute->handle($request);
        }

        $handler = new QueueableRequestHandler($this->matchedRoute);
        $handler->add(...$this->middlewares);

        return $handler->handle($request);
    }

    /**
     * Loads routes through the given loaders
     *
     * @param LoaderInterface ...$loaders
     *
     * @return void
     */
    public function load(LoaderInterface ...$loaders): void
    {
        foreach ($loaders as $loader) {
            $this->routes->add(...$loader->load()->all());
        }
    }
}
