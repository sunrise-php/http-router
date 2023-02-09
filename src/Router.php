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
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\Exception\PageNotFoundException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Sunrise\Http\Router\RequestHandler\UnsafeCallableRequestHandler;
use Generator;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_build;
use function Sunrise\Http\Router\path_match;
use function sprintf;

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
     * @var array<string, list<string>>
     */
    private array $hosts = [];

    /**
     * The router's routes
     *
     * @var array<string, array<string, RouteInterface>>
     */
    private array $routes = [];

    /**
     * The router's middlewares
     *
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * The router's matched route
     *
     * @var RouteInterface|null
     */
    private ?RouteInterface $matchedRoute = null;

    /**
     * The router's event dispatcher
     *
     * @var EventDispatcherInterface|null
     */
    private ?EventDispatcherInterface $eventDispatcher = null;

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
     * Adds the given host to the router's host table
     *
     * @param string $alias
     * @param string ...$hostnames
     *
     * @return void
     *
     * @since 2.6.0
     */
    public function addHost(string $alias, string ...$hostnames): void
    {
        foreach ($hostnames as $hostname) {
            $this->hosts[$alias][] = $hostname;
        }
    }

    /**
     * Adds the given hosts to the router's host table
     *
     * @param array<string, list<string>> $hosts
     *
     * @return void
     *
     * @since 2.11.0
     */
    public function addHosts(array $hosts): void
    {
        foreach ($hosts as $alias => $hostnames) {
            foreach ($hostnames as $hostname) {
                $this->hosts[$alias][] = $hostname;
            }
        }
    }

    /**
     * Gets the router's host table
     *
     * @return array<string, list<string>>
     *
     * @since 2.6.0
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    /**
     * Resolves the given hostname to its alias
     *
     * @param string $hostname
     *
     * @return string|null
     *
     * @since 2.14.0
     */
    public function resolveHostname(string $hostname): ?string
    {
        foreach ($this->hosts as $alias => $hostnames) {
            foreach ($hostnames as $value) {
                if ($hostname === $value) {
                    return $alias;
                }
            }
        }

        return null;
    }

    /**
     * Adds the given route(s) to the router
     *
     * @param RouteInterface ...$routes
     *
     * @return void
     */
    public function addRoute(RouteInterface ...$routes): void
    {
        foreach ($routes as $route) {
            $host = $route->getHost() ?? '*';
            $name = $route->getName();

            $this->routes[$host][$name] = $route;
        }
    }

    /**
     * Gets all routes
     *
     * @return Generator<RouteInterface>
     */
    public function getRoutes(): Generator
    {
        foreach ($this->routes as $routes) {
            foreach ($routes as $route) {
                yield $route;
            }
        }
    }

    /**
     * Gets routes by the given host
     *
     * @param string $host
     *
     * @return Generator<RouteInterface>
     *
     * @since 3.0.0
     */
    public function getRoutesByHost(string $host): Generator
    {
        if (isset($this->routes[$host])) {
            foreach ($this->routes[$host] as $route) {
                yield $route;
            }
        }
    }

    /**
     * Gets routes by the given hostname
     *
     * @param string $hostname
     *
     * @return Generator<RouteInterface>
     *
     * @since 2.14.0
     */
    public function getRoutesByHostname(string $hostname): Generator
    {
        $host = $this->resolveHostname($hostname);

        if (isset($host) && isset($this->routes[$host])) {
            foreach ($this->routes[$host] as $route) {
                yield $route;
            }
        }

        if (isset($this->routes['*'])) {
            foreach ($this->routes['*'] as $route) {
                yield $route;
            }
        }
    }

    /**
     * Gets a route by the given name
     *
     * @param string $name
     *
     * @return RouteInterface
     *
     * @throws RouteNotFoundException
     *         If a route wasn't found by the given name.
     */
    public function getRoute(string $name): RouteInterface
    {
        foreach ($this->routes as $routes) {
            if (isset($routes[$name])) {
                return $routes[$name];
            }
        }

        throw new RouteNotFoundException(sprintf(
            'No route found for name "%s"',
            $name
        ));
    }

    /**
     * Gets a route by the given name and host
     *
     * @param string $name
     * @param string $host
     *
     * @return RouteInterface
     *
     * @throws RouteNotFoundException
     *         If a route wasn't found by the given name and host.
     *
     * @since 3.0.0
     */
    public function getNamedRouteByHost(string $name, string $host): RouteInterface
    {
        if (isset($this->routes[$host][$name])) {
            return $this->routes[$host][$name];
        }

        throw new RouteNotFoundException(sprintf(
            'No route found for name "%s" and host "%s"',
            $name,
            $host
        ));
    }

    /**
     * Gets a route by the given name and hostname
     *
     * @param string $name
     * @param string $hostname
     *
     * @return RouteInterface
     *
     * @throws RouteNotFoundException
     *         If a route wasn't found by the given name and hostname.
     *
     * @since 3.0.0
     */
    public function getNamedRouteByHostname(string $name, string $hostname): RouteInterface
    {
        $host = $this->resolveHostname($hostname);

        if (isset($host) && isset($this->routes[$host][$name])) {
            return $this->routes[$host][$name];
        }

        if (isset($this->routes['*'][$name])) {
            return $this->routes['*'][$name];
        }

        throw new RouteNotFoundException(sprintf(
            'No route found for name "%s" and hostname "%s"',
            $name,
            $hostname
        ));
    }

    /**
     * Checks if a route exists by the given name
     *
     * @return bool
     */
    public function hasRoute(string $name): bool
    {
        foreach ($this->routes as $routes) {
            if (isset($routes[$name])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a route exists by the given name and host
     *
     * @return bool
     *
     * @since 3.0.0
     */
    public function existsNamedRouteByHost(string $name, string $host): bool
    {
        if (isset($this->routes[$host][$name])) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a route exists by the given name and hostname
     *
     * @return bool
     *
     * @since 3.0.0
     */
    public function existsNamedRouteByHostname(string $name, string $hostname): bool
    {
        if (isset($this->routes['*'][$name])) {
            return true;
        }

        $host = $this->resolveHostname($hostname);

        if (isset($host) && isset($this->routes[$host][$name])) {
            return true;
        }

        return false;
    }

    /**
     * Gets allowed methods
     *
     * @return list<string>
     */
    public function getAllowedMethods(): array
    {
        $methods = [];
        foreach ($this->routes as $routes) {
            foreach ($routes as $route) {
                foreach ($route->getMethods() as $method) {
                    $methods[$method] = $method;
                }
            }
        }

        return empty($methods) ? [] : \array_values($methods);
    }

    /**
     * Gets allowed methods by the given host
     *
     * @param string $host
     *
     * @return list<string>
     *
     * @since 3.0.0
     */
    public function getAllowedMethodsByHost(string $host): array
    {
        $methods = [];
        if (isset($this->routes[$host])) {
            foreach ($this->routes[$host] as $route) {
                foreach ($route->getMethods() as $method) {
                    $methods[$method] = $method;
                }
            }
        }

        return empty($methods) ? [] : \array_values($methods);
    }

    /**
     * Gets allowed methods by the given hostname
     *
     * @param string $hostname
     *
     * @return list<string>
     *
     * @since 3.0.0
     */
    public function getAllowedMethodsByHostname(string $hostname): array
    {
        $methods = [];
        if (isset($this->routes['*'])) {
            foreach ($this->routes['*'] as $route) {
                foreach ($route->getMethods() as $method) {
                    $methods[$method] = $method;
                }
            }
        }

        $host = $this->resolveHostname($hostname);
        if (isset($host) && isset($this->routes[$host])) {
            foreach ($this->routes[$host] as $route) {
                foreach ($route->getMethods() as $method) {
                    $methods[$method] = $method;
                }
            }
        }

        return empty($methods) ? [] : \array_values($methods);
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
     *
     * @throws RouteNotFoundException
     *         If a route wasn't found by the given name.
     *
     * @throws Exception\RoutePathBuildException
     *         If a required attribute value is not given,
     *         or if an attribute value is not valid in strict mode.
     */
    public function generateUri(string $name, array $attributes = [], bool $strict = false): string
    {
        $route = $this->getRoute($name);

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
     *         If the request URI cannot be matched against any route.
     *
     * @throws MethodNotAllowedException
     *         If the request method isn't allowed.
     */
    public function match(ServerRequestInterface $request): RouteInterface
    {
        $requestUri = $request->getUri();
        $requestHost = $requestUri->getHost();
        $requestPath = $requestUri->getPath();
        $requestMethod = $request->getMethod();
        $allowedMethods = [];

        $routes = $this->getRoutesByHostname($requestHost);

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

            // $routeConsumedContentTypes = $route->getConsumedContentTypes();
            // $routeProducedContentTypes = $route->getProducedContentTypes();

            /** @var array<string, string> $attributes */

            return $route->withAddedAttributes($attributes);
        }

        if (!empty($allowedMethods)) {
            throw new MethodNotAllowedException($requestMethod, $allowedMethods);
        }

        throw new PageNotFoundException();
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
            $this->addRoute(...$loader->load()->all());
        }
    }
}
