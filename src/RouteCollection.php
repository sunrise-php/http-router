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

/**
 * Import functions
 */
use function array_merge;

/**
 * RouteCollection
 *
 * Use the {@see RouteCollectionFactory} factory to create this class.
 */
class RouteCollection implements RouteCollectionInterface
{

    /**
     * The collection routes
     *
     * @var RouteInterface[]
     */
    private $routes;

    /**
     * Constructor of the class
     *
     * @param RouteInterface ...$routes
     */
    public function __construct(RouteInterface ...$routes)
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function all() : array
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name) : ?RouteInterface
    {
        foreach ($this->routes as $route) {
            if ($name === $route->getName()) {
                return $route;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name) : bool
    {
        return $this->get($name) instanceof RouteInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function add(RouteInterface ...$routes) : RouteCollectionInterface
    {
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHost(string $host) : RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setHost($host);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPrefix(string $prefix) : RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addPrefix($prefix);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSuffix(string $suffix) : RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addSuffix($suffix);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMethod(string ...$methods) : RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addMethod(...$methods);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addMiddleware(...$middlewares);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prependMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setMiddlewares(...array_merge($middlewares, $route->getMiddlewares()));
        }

        return $this;
    }

    /**
     * @deprecated 2.12.0 Use the addMiddleware method.
     */
    public function appendMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionInterface
    {
        return $this->addMiddleware(...$middlewares);
    }

    /**
     * @deprecated 2.10.0 Use the prependMiddleware method.
     */
    public function unshiftMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionInterface
    {
        return $this->prependMiddleware(...$middlewares);
    }
}
