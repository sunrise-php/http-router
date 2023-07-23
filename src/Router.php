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
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ServerRequestProxy;
use Sunrise\Http\Router\Event\RouteEvent;
use Sunrise\Http\Router\Exception\ClientNotConsumedMediaTypeException;
use Sunrise\Http\Router\Exception\ClientNotProducedMediaTypeException;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\PageNotFoundException;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Sunrise\Http\Router\RequestHandler\UnsafeCallableRequestHandler;

use function array_keys;
use function Sunrise\Http\Router\path_build;
use function Sunrise\Http\Router\path_match;

/**
 * Router
 */
class Router implements RequestHandlerInterface, RequestMethodInterface
{

    /**
     * @var array<string, string>
     *
     * @since 2.9.0
     */
    public static array $patterns = [
        '@slug' => '[0-9a-z-]+',
        '@uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
    ];

    /**
     * @var HostTable
     */
    private HostTable $hosts;

    /**
     * @var RouteCollectionInterface
     */
    private RouteCollectionInterface $routes;

    /**
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * @var EventDispatcherInterface|null
     */
    private ?EventDispatcherInterface $eventDispatcher = null;

    /**
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
     * Gets the router's middlewares
     *
     * @return list<MiddlewareInterface>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
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
     * Gets the router's matched route
     *
     * @return RouteInterface|null
     *
     * @since 2.12.0
     */
    public function getMatchedRoute(): ?RouteInterface
    {
        return $this->matchedRoute;
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
     * @throws PageNotFoundException
     *         If the request URI isn't served.
     *
     * @throws MethodNotAllowedException
     *         If the request method isn't allowed.
     *
     * @throws ClientNotProducedMediaTypeException
     *         If the client not produces required media types.
     *
     * @throws ClientNotConsumedMediaTypeException
     *         If the client not consumed provided media types.
     */
    public function match(ServerRequestInterface $request): RouteInterface
    {
        $requestUri = $request->getUri();
        $requestHost = $requestUri->getHost();
        $requestPath = $requestUri->getPath();
        $requestMethod = $request->getMethod();
        $allowedMethods = [];

        $host = $this->hosts->resolve($requestHost);
        $routes = $this->routes->allOnHost($host);
        $request = ServerRequest::from($request);

        foreach ($routes as $route) {
            // https://github.com/sunrise-php/http-router/issues/50
            // https://tools.ietf.org/html/rfc7231#section-6.5.5
            if (!path_match($route->getPath(), $requestPath, $attributes)) {
                continue;
            }

            $routeMethods = [];
            foreach ($route->getMethods() as $routeMethod) {
                $routeMethods[$routeMethod] = true;
                $allowedMethods[$routeMethod] = true;
            }

            if (!isset($routeMethods[$requestMethod])) {
                continue;
            }

            $routeConsumes = $route->getConsumedMediaTypes();
            if (!empty($routeConsumes) && !$request->clientProducesMediaType($routeConsumes)) {
                throw new ClientNotProducedMediaTypeException($routeConsumes);
            }

            $routeProduces = $route->getProducedMediaTypes();
            if (!empty($routeProduces) && !$request->clientConsumesMediaType($routeProduces)) {
                throw new ClientNotConsumedMediaTypeException($routeProduces);
            }

            /** @var array<string, string> $attributes */

            return $route->withAddedAttributes($attributes);
        }

        if (!empty($allowedMethods)) {
            $allowedMethods = array_keys($allowedMethods);

            throw new MethodNotAllowedException($allowedMethods);
        }

        throw new PageNotFoundException('Page Not Found');
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
                    $event = new RouteEvent($this->matchedRoute, $request);
                    $this->eventDispatcher->dispatch($event);
                    $request = $event->getRequest();
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
            $event = new RouteEvent($this->matchedRoute, $request);
            $this->eventDispatcher->dispatch($event);
            $request = $event->getRequest();
        }

        if (empty($this->middlewares)) {
            return $this->matchedRoute->handle($request);
        }

        $handler = new QueueableRequestHandler($this->matchedRoute);
        $handler->add(...$this->middlewares);

        return $handler->handle($request);
    }
}
