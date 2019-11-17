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
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Import functions
 */
use function array_merge;
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
        return $this->routes;
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
            $this->routes[] = $route;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @todo Maybe create a route factory?
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
            ['HEAD'],
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
            ['GET'],
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
            ['POST'],
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
            ['PUT'],
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
            ['PATCH'],
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
            ['DELETE'],
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
            ['PURGE'],
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
