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
use Sunrise\Http\Router\Exception\ExceptionFactory;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;

/**
 * Import functions
 */
use function array_keys;
use function array_values;
use function spl_object_hash;

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
     * Adds the given route(s) to the router
     *
     * @param RouteInterface ...$routes
     *
     * @return void
     *
     * @throws Exception\RouteAlreadyExistsException
     */
    public function addRoute(RouteInterface ...$routes) : void
    {
        foreach ($routes as $route) {
            $name = $route->getName();

            if (isset($this->routes[$name])) {
                throw (new ExceptionFactory)->routeAlreadyExists($name, [
                    'route' => $route,
                ]);
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
     * @throws Exception\MiddlewareAlreadyExistsException
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : void
    {
        foreach ($middlewares as $middleware) {
            $hash = spl_object_hash($middleware);

            if (isset($this->middlewares[$hash])) {
                throw (new ExceptionFactory)->middlewareAlreadyExists($hash, [
                    'middleware' => $middleware,
                ]);
            }

            $this->middlewares[$hash] = $middleware;
        }
    }

    /**
     * Gets allowed HTTP methods
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
     * @throws Exception\RouteNotFoundException
     */
    public function getRoute(string $name) : RouteInterface
    {
        if (!isset($this->routes[$name])) {
            throw (new ExceptionFactory)->routeNotFoundByName($name);
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
     * @throws Exception\RouteNotFoundException
     *
     * @throws Exception\InvalidAttributeValueException May be thrown from `path_build`.
     * @throws Exception\MissingAttributeValueException May be thrown from `path_build`.
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
     * @throws Exception\MethodNotAllowedException
     * @throws Exception\RouteNotFoundException
     */
    public function match(ServerRequestInterface $request) : RouteInterface
    {
        $routes = [];
        foreach ($this->routes as $route) {
            foreach ($route->getMethods() as $method) {
                $routes[$method][] = $route;
            }
        }

        $requestedMethod = $request->getMethod();
        if (!isset($routes[$requestedMethod])) {
            throw (new ExceptionFactory)->methodNotAllowed($requestedMethod, $this->getAllowedMethods(), [
                'request' => $request,
            ]);
        }

        $requestedUri = $request->getUri()->getPath();
        foreach ($routes[$requestedMethod] as $route) {
            if (path_match($route->getPath(), $requestedUri, $attributes)) {
                return $route->withAddedAttributes($attributes);
            }
        }

        throw (new ExceptionFactory)->routeNotFoundByUri($requestedUri, [
            'request' => $request,
        ]);
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
        } catch (ExceptionInterface $e) {
            return $handler->handle(
                $request->withAttribute(self::ATTR_NAME_FOR_ROUTING_ERROR, $e)
            );
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
