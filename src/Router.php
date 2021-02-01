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
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\MiddlewareAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;

/**
 * Import functions
 */
use function array_flip;
use function array_keys;
use function array_values;
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
     * The router host table
     *
     * @var array
     */
    private $hosts = [];

    /**
     * The router routes
     *
     * @var RouteInterface[]
     */
    private $routes = [];

    /**
     * The router middlewares
     *
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    /**
     * Gets the router host table
     *
     * @return array
     *
     * @since 2.6.0
     */
    public function getHosts() : array
    {
        return $this->hosts;
    }

    /**
     * Gets the router routes
     *
     * @return RouteInterface[]
     */
    public function getRoutes() : array
    {
        return array_values($this->routes);
    }

    /**
     * Gets the router middlewares
     *
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares() : array
    {
        return array_values($this->middlewares);
    }

    /**
     * Adds the given host alias to the router host table
     *
     * @param string $alias
     * @param string $host
     *
     * @return void
     *
     * @since 2.6.0
     */
    public function addHost(string $alias, string $host) : void
    {
        $this->hosts[$alias] = $host;
    }

    /**
     * Adds the given route(s) to the router
     *
     * @param RouteInterface ...$routes
     *
     * @return void
     *
     * @throws RouteAlreadyExistsException
     */
    public function addRoute(RouteInterface ...$routes) : void
    {
        foreach ($routes as $route) {
            $name = $route->getName();

            if (isset($this->routes[$name])) {
                throw new RouteAlreadyExistsException(
                    sprintf('A route with the name "%s" already exists.', $name)
                );
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
     * @throws MiddlewareAlreadyExistsException
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : void
    {
        foreach ($middlewares as $middleware) {
            $hash = spl_object_hash($middleware);

            if (isset($this->middlewares[$hash])) {
                throw new MiddlewareAlreadyExistsException(
                    sprintf('A middleware with the hash "%s" already exists.', $hash)
                );
            }

            $this->middlewares[$hash] = $middleware;
        }
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
            throw new RouteNotFoundException(
                sprintf('No route found for the name "%s".', $name)
            );
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
     * @throws RouteNotFoundException
     */
    public function match(ServerRequestInterface $request) : RouteInterface
    {
        $requestHost = $request->getUri()->getHost();
        $requestPath = $request->getUri()->getPath();
        $requestMethod = $request->getMethod();
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (!$this->compareHosts($route->getHost(), $requestHost)) {
                continue;
            }

            // https://github.com/sunrise-php/http-router/issues/50
            // https://tools.ietf.org/html/rfc7231#section-6.5.5
            if (!path_match($route->getPath(), $requestPath, $attributes)) {
                continue;
            }

            $routeMethods = array_flip($route->getMethods());
            $allowedMethods += $routeMethods;

            if (!isset($routeMethods[$requestMethod])) {
                continue;
            }

            return $route->withAddedAttributes($attributes);
        }

        if (!empty($allowedMethods)) {
            throw new MethodNotAllowedException('Method Not Allowed', [
                'allowed' => array_keys($allowedMethods),
            ]);
        }

        throw new RouteNotFoundException('Route Not Found');
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $route = $this->match($request);

        $handler = new QueueableRequestHandler($route);
        $handler->add(...$this->getMiddlewares());

        return $handler->handle($request);
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            return $this->handle($request);
        } catch (MethodNotAllowedException | RouteNotFoundException $e) {
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

    /**
     * Compares the given route host and the given request host
     *
     * Returns `true` if the route host is `null`
     * or if the route host is equal to the request host,
     * otherwise returns `false`.
     *
     * @param null|string $routeHost
     * @param string $requestHost
     *
     * @return bool
     */
    private function compareHosts(?string $routeHost, string $requestHost) : bool
    {
        if (null === $routeHost) {
            return true;
        }

        // trying to resolve the route host....
        if (isset($this->hosts[$routeHost])) {
            $routeHost = $this->hosts[$routeHost];
        }

        if ($requestHost === $routeHost) {
            return true;
        }

        return false;
    }
}
