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
use Psr\Http\Server\MiddlewareInterface;

/**
 * Import functions
 */
use function count;

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
     * @var array<string, RouteInterface>
     */
    private array $routes = [];

    /**
     * Constructor of the class
     *
     * @param RouteInterface ...$routes
     */
    public function __construct(RouteInterface ...$routes)
    {
        $this->add(...$routes);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        $routes = [];
        foreach ($this->routes as $route) {
            $routes[] = $route;
        }

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name): ?RouteInterface
    {
        return $this->routes[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return isset($this->routes[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function add(RouteInterface ...$routes): RouteCollectionInterface
    {
        foreach ($routes as $route) {
            $this->routes[$route->getName()] = $route;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHost(string $host): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setHost($host);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConsumedContentTypes(string ...$contentTypes): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setConsumedContentTypes(...$contentTypes);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProducedContentTypes(string ...$contentTypes): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setProducedContentTypes(...$contentTypes);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute(string $name, $value): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPrefix(string $prefix): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addPrefix($prefix);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSuffix(string $suffix): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addSuffix($suffix);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMethod(string ...$methods): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addMethod(...$methods);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addConsumedContentType(string ...$contentTypes): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addConsumedContentType(...$contentTypes);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addProducedContentType(string ...$contentTypes): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addProducedContentType(...$contentTypes);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addMiddleware(...$middlewares);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPriorityMiddleware(MiddlewareInterface ...$middlewares): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addPriorityMiddleware(...$middlewares);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTag(string ...$tags): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addTag(...$tags);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->routes);
    }
}
