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
 * Use the factory to create this class.
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
    public function add(RouteInterface ...$routes) : void
    {
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }
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
     *
     * @since 2.9.0
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
     *
     * @since 2.9.0
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
     *
     * @since 2.9.0
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
     *
     * @since 2.9.0
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
     *
     * @since 2.9.0
     */
    public function appendMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addMiddleware(...$middlewares);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since 2.9.0
     */
    public function prependMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setMiddlewares(...array_merge($middlewares, $route->getMiddlewares()));
        }

        return $this;
    }

    /**
     * BC (backward compatibility) for version less than 2.9.0
     *
     * @see appendMiddleware
     *
     * @deprecated 2.9.0 Use the `appendMiddleware` method.
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionInterface
    {
        $this->appendMiddleware(...$middlewares);

        return $this;
    }

    /**
     * BC (backward compatibility) for version less than 2.9.0
     *
     * @see prependMiddleware
     *
     * @deprecated 2.9.0 Use the `prependMiddleware` method.
     */
    public function unshiftMiddleware(MiddlewareInterface ...$middlewares) : RouteCollectionInterface
    {
        $this->prependMiddleware(...$middlewares);

        return $this;
    }
}
