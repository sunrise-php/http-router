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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\RequestHandler\RoutableRequestHandler;

/**
 * Import functions
 */
use function array_keys;

/**
 * Router
 */
class Router extends RouteCollection implements MiddlewareInterface, RequestHandlerInterface
{

    /**
     * Server Request attribute name for routing error instance
     *
     * @var string
     */
    public const ATTR_NAME_FOR_ROUTING_ERROR = '@routing-error';

    /**
     * Generates a URI for the given named route
     *
     * @param string $name
     * @param array $attributes
     * @param bool $strict
     *
     * @return string
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
        $routes = [];
        foreach ($this->getRoutes() as $route) {
            foreach ($route->getMethods() as $method) {
                $routes[$method][] = $route;
            }
        }

        $method = $request->getMethod();
        if (!isset($routes[$method])) {
            throw new MethodNotAllowedException(
                array_keys($routes)
            );
        }

        $target = $request->getUri()->getPath();
        foreach ($routes[$method] as $route) {
            if (path_match($route->getPath(), $target, $attributes)) {
                return $route->withAddedAttributes($attributes);
            }
        }

        throw new RouteNotFoundException();
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $route = $this->match($request);

        $requestHandler = new RoutableRequestHandler($route);

        return $requestHandler->handle($request);
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
}
