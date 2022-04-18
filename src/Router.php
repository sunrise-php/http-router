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
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Event\RouteEvent;
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\PageNotFoundException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_build;
use function Sunrise\Http\Router\path_match;
use function array_keys;
use function get_class;
use function spl_object_hash;
use function sprintf;

/**
 * Router
 */
class Router implements MiddlewareInterface, RequestHandlerInterface, RequestMethodInterface
{

    /**
     * Server Request attribute name for routing error instance
     *
     * @var string
     */
    public const ATTR_NAME_FOR_ROUTING_ERROR = '@routing-error';

    /**
     * Global patterns
     *
     * @var array<string, string>
     *
     * @since 2.9.0
     */
    public static $patterns = [
        '@slug' => '[0-9a-z-]+',
        '@uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
    ];

    /**
     * The router's host table
     *
     * @var array<string, string[]>
     *      The key is a host alias and values are hostnames.
     */
    private $hosts = [];

    /**
     * The router's routes
     *
     * @var array<string, RouteInterface>
     *      The key is a route name.
     */
    private $routes = [];

    /**
     * The router's middlewares
     *
     * @var array<string, MiddlewareInterface>
     *      The keys is an object hash.
     */
    private $middlewares = [];

    /**
     * The router's matched route
     *
     * @var RouteInterface|null
     */
    private $matchedRoute = null;

    /**
     * The router's event dispatcher
     *
     * @var EventDispatcherInterface|null
     *
     * @since 2.13.0
     */
    private $eventDispatcher = null;

    /**
     * Gets the router's host table
     *
     * @return array<string, string[]>
     *
     * @since 2.6.0
     */
    public function getHosts() : array
    {
        return $this->hosts;
    }

    /**
     * Resolves the given hostname
     *
     * @param string $hostname
     *
     * @return string|null
     *
     * @since 2.14.0
     */
    public function resolveHostname(string $hostname) : ?string
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
     * Gets all routes
     *
     * @return RouteInterface[]
     */
    public function getRoutes() : array
    {
        $routes = [];
        foreach ($this->routes as $route) {
            $routes[] = $route;
        }

        return $routes;
    }

    /**
     * Gets routes by the given hostname
     *
     * @param string $hostname
     *
     * @return RouteInterface[]
     *
     * @since 2.14.0
     */
    public function getRoutesByHostname(string $hostname) : array
    {
        // the hostname's alias.
        $alias = $this->resolveHostname($hostname);

        $routes = [];
        foreach ($this->routes as $route) {
            $host = $route->getHost();
            if ($host === null || $host === $alias) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    /**
     * Gets the router's middlewares
     *
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares() : array
    {
        $middlewares = [];
        foreach ($this->middlewares as $middleware) {
            $middlewares[] = $middleware;
        }

        return $middlewares;
    }

    /**
     * Gets the router's matched route
     *
     * @return RouteInterface|null
     */
    public function getMatchedRoute() : ?RouteInterface
    {
        return $this->matchedRoute;
    }

    /**
     * Gets the router's event dispatcher
     *
     * @return EventDispatcherInterface|null
     *
     * @since 2.13.0
     */
    public function getEventDispatcher() : ?EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * Adds the given patterns to the router
     *
     * ```php
     * $router->addPatterns([
     *   '@digit' => '\d+',
     *   '@word' => '\w+',
     * ]);
     * ```
     *
     * ```php
     * $route->setPath('/{foo<@digit>}/{bar<@word>}');
     * ```
     *
     * @param array<string, string> $patterns
     *
     * @return void
     *
     * @since 2.11.0
     */
    public function addPatterns(array $patterns) : void
    {
        foreach ($patterns as $alias => $pattern) {
            self::$patterns[$alias] = $pattern;
        }
    }

    /**
     * Adds aliases for hostnames to the router's host table
     *
     * ```php
     * $router->addHosts([
     *   'local' => ['127.0.0.1', 'localhost'],
     * ]);
     * ```
     *
     * ```php
     * // will be available at 127.0.0.1
     * $route->setHost('local');
     * ```
     *
     * @param array<string, string[]> $hosts
     *
     * @return void
     *
     * @since 2.11.0
     */
    public function addHosts(array $hosts) : void
    {
        foreach ($hosts as $alias => $hostnames) {
            $this->addHost($alias, ...$hostnames);
        }
    }

    /**
     * Adds the given alias for the given hostname(s) to the router's host table
     *
     * @param string $alias
     * @param string ...$hostnames
     *
     * @return void
     *
     * @since 2.6.0
     */
    public function addHost(string $alias, string ...$hostnames) : void
    {
        $this->hosts[$alias] = $hostnames;
    }

    /**
     * Adds the given route(s) to the router
     *
     * @param RouteInterface ...$routes
     *
     * @return void
     *
     * @throws InvalidArgumentException
     *         if one of the given routes already exists.
     */
    public function addRoute(RouteInterface ...$routes) : void
    {
        foreach ($routes as $route) {
            $name = $route->getName();
            if (isset($this->routes[$name])) {
                throw new InvalidArgumentException(sprintf(
                    'The route "%s" already exists.',
                    $name
                ));
            }

            $this->routes[$name] = $route;
        }
    }

    /**
     * Adds the given middleware(s) to the router
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return void
     *
     * @throws InvalidArgumentException
     *         if one of the given middlewares already exists.
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : void
    {
        foreach ($middlewares as $middleware) {
            $hash = spl_object_hash($middleware);
            if (isset($this->middlewares[$hash])) {
                throw new InvalidArgumentException(sprintf(
                    'The middleware "%s" already exists.',
                    get_class($middleware)
                ));
            }

            $this->middlewares[$hash] = $middleware;
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
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher) : void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Gets allowed methods
     *
     * @return string[]
     */
    public function getAllowedMethods() : array
    {
        $methods = [];
        foreach ($this->routes as $route) {
            foreach ($route->getMethods() as $method) {
                $methods[$method] = true;
            }
        }

        return array_keys($methods);
    }

    /**
     * Checks if a route exists by the given name
     *
     * @return bool
     */
    public function hasRoute(string $name) : bool
    {
        return isset($this->routes[$name]);
    }

    /**
     * Gets a route for the given name
     *
     * @param string $name
     *
     * @return RouteInterface
     *
     * @throws RouteNotFoundException
     */
    public function getRoute(string $name) : RouteInterface
    {
        if (!isset($this->routes[$name])) {
            throw new RouteNotFoundException(sprintf(
                'No route found for the name "%s".',
                $name
            ));
        }

        return $this->routes[$name];
    }

    /**
     * Generates a URI for the given named route
     *
     * @param string $name
     * @param array $attributes
     * @param bool $strict
     *
     * @return string
     *
     * @throws RouteNotFoundException
     *         If the given named route wasn't found.
     *
     * @throws Exception\InvalidAttributeValueException
     *         It can be thrown in strict mode, if an attribute value is not valid.
     *
     * @throws Exception\MissingAttributeValueException
     *         If a required attribute value is not given.
     */
    public function generateUri(string $name, array $attributes = [], bool $strict = false) : string
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
     * @throws MethodNotAllowedException
     * @throws PageNotFoundException
     */
    public function match(ServerRequestInterface $request) : RouteInterface
    {
        $currentHost = $request->getUri()->getHost();
        $currentPath = $request->getUri()->getPath();
        $currentMethod = $request->getMethod();
        $allowedMethods = [];

        $routes = $this->getRoutesByHostname($currentHost);

        foreach ($routes as $route) {
            // https://github.com/sunrise-php/http-router/issues/50
            // https://tools.ietf.org/html/rfc7231#section-6.5.5
            if (!path_match($route->getPath(), $currentPath, $attributes)) {
                continue;
            }

            $routeMethods = [];
            foreach ($route->getMethods() as $routeMethod) {
                $routeMethods[$routeMethod] = true;
                $allowedMethods[$routeMethod] = true;
            }

            if (!isset($routeMethods[$currentMethod])) {
                continue;
            }

            return $route->withAddedAttributes($attributes);
        }

        if (!empty($allowedMethods)) {
            throw new MethodNotAllowedException('Method Not Allowed', [
                'method' => $currentMethod,
                'allowed' => array_keys($allowedMethods),
            ]);
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
    public function run(ServerRequestInterface $request) : ResponseInterface
    {
        // lazy resolving of the given request...
        $routing = new CallableRequestHandler(function (ServerRequestInterface $request) : ResponseInterface {
            $route = $this->match($request);
            $this->matchedRoute = $route;

            if (isset($this->eventDispatcher)) {
                $event = new RouteEvent($route, $request);

                /**
                 * @psalm-suppress TooManyArguments
                 */
                $this->eventDispatcher->dispatch($event, RouteEvent::NAME);

                $request = $event->getRequest();
            }

            return $route->handle($request);
        });

        $middlewares = $this->getMiddlewares();
        if (empty($middlewares)) {
            return $routing->handle($request);
        }

        $handler = new QueueableRequestHandler($routing);
        $handler->add(...$middlewares);

        return $handler->handle($request);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $route = $this->match($request);
        $this->matchedRoute = $route;

        if (isset($this->eventDispatcher)) {
            $event = new RouteEvent($route, $request);

            /**
             * @psalm-suppress TooManyArguments
             */
            $this->eventDispatcher->dispatch($event, RouteEvent::NAME);

            $request = $event->getRequest();
        }

        $middlewares = $this->getMiddlewares();
        if (empty($middlewares)) {
            return $route->handle($request);
        }

        $handler = new QueueableRequestHandler($route);
        $handler->add(...$middlewares);

        return $handler->handle($request);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            return $this->handle($request);
        } catch (MethodNotAllowedException|PageNotFoundException $e) {
            $request = $request->withAttribute(self::ATTR_NAME_FOR_ROUTING_ERROR, $e);

            return $handler->handle($request);
        }
    }

    /**
     * Loads routes through the given loaders
     *
     * @param LoaderInterface ...$loaders
     *
     * @return void
     */
    public function load(LoaderInterface ...$loaders) : void
    {
        foreach ($loaders as $loader) {
            $this->addRoute(...$loader->load()->all());
        }
    }
}
