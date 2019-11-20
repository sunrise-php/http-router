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
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\RouteAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;

/**
 * Import functions
 */
use function array_merge;
use function array_values;
use function rtrim;

/**
 * RouteCollection
 */
class RouteCollection implements RouteCollectionInterface
{

    /**
     * The collection prefix
     *
     * @var null|string
     */
    private $prefix;

    /**
     * The collection middlewares
     *
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    /**
     * The collection routes
     *
     * @var RouteInterface[]
     */
    private $routes = [];

    /**
     * {@inheritDoc}
     */
    public function getPrefix() : ?string
    {
        return $this->prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function getMiddlewares() : array
    {
        return $this->middlewares;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutes() : array
    {
        return array_values($this->routes);
    }

    /**
     * {@inheritDoc}
     */
    public function getRoute(string $name) : RouteInterface
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }

        throw new RouteNotFoundException();
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefix(string $prefix) : RouteCollectionInterface
    {
        // https://github.com/sunrise-php/http-router/issues/26
        $prefix = rtrim($prefix, '/');

        $this->prefix = $prefix;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addMiddlewares(MiddlewareInterface ...$middlewares) : RouteCollectionInterface
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addRoutes(RouteInterface ...$routes) : RouteCollectionInterface
    {
        foreach ($routes as $route) {
            $name = $route->getName();

            if (isset($this->routes[$name])) {
                throw new RouteAlreadyExistsException();
            }

            $this->routes[$name] = $route;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function route(
        string $name,
        string $path,
        array $methods,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        $path = $this->prefix . $path;

        $middlewares = array_merge(
            $this->middlewares,
            $middlewares
        );

        $route = new Route(
            $name,
            $path,
            $methods,
            $requestHandler,
            $middlewares,
            $attributes
        );

        $this->addRoutes($route);

        return $route;
    }

    /**
     * {@inheritDoc}
     */
    public function head(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_HEAD],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

    /**
     * {@inheritDoc}
     */
    public function get(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_GET],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

    /**
     * {@inheritDoc}
     */
    public function post(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_POST],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

    /**
     * {@inheritDoc}
     */
    public function put(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_PUT],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

    /**
     * {@inheritDoc}
     */
    public function patch(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_PATCH],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

    /**
     * {@inheritDoc}
     */
    public function delete(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_DELETE],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

    /**
     * {@inheritDoc}
     */
    public function purge(
        string $name,
        string $path,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_PURGE],
            $requestHandler,
            $middlewares,
            $attributes
        );
    }

    /**
     * {@inheritDoc}
     */
    public function group(string $prefix, callable $callback) : void
    {
        $children = new self;
        $children->setPrefix($this->prefix . $prefix);
        $children->addMiddlewares(...$this->middlewares);

        $callback($children);

        $this->addRoutes(...$children->getRoutes());
    }
}
