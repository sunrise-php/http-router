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
use function array_keys;
use function array_values;
use function spl_object_hash;
use function sprintf;
use function array_unique;
use function array_merge;
use function in_array;

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
            $methods = $route->getMethods();
        }

        return array_unique(array_merge(...$methods));
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
        $method = $request->getMethod();
        $allowedMethods = $this->getAllowedMethods();
        if (!in_array($method, $allowedMethods)) {
            $errmsg = sprintf('The method "%s" not allowed.', $method);
            throw new MethodNotAllowedException($errmsg, ['allowed' => $allowedMethods]);
        }

        $target = $request->getUri()->getPath();
        foreach ($this->routes as $route) {
            if (!in_array($method, $route->getMethods())) {
                continue;
            }
            if (path_match($route->getPath(), $target, $attributes)) {
                return $route->withAddedAttributes($attributes);
            }
        }

        throw new RouteNotFoundException(
            sprintf('Route not found for the request "%s".', $target)
        );
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
            return $handler->handle(
                $request->withAttribute(static::ATTR_NAME_FOR_ROUTING_ERROR, $e)
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
