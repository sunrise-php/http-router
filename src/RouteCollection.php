<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router;

use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Exception\RouteAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Iterator;

use function count;
use function sprintf;

/**
 * RouteCollection
 *
 * Use the {@see RouteCollectionFactory} factory to create this class.
 */
class RouteCollection implements RouteCollectionInterface
{

    /**
     * @var string
     */
    private const ANY = '*';

    /**
     * @var array<string, RouteInterface>
     */
    private array $routes = [];

    /**
     * @var array<string, list<string>>
     */
    private array $hostMap = [];

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
     * @inheritDoc
     */
    public function getIterator(): Iterator
    {
        foreach ($this->routes as $route) {
            yield $route;
        }
    }

    /**
     * @inheritDoc
     */
    public function all(): Iterator
    {
        foreach ($this->routes as $route) {
            yield $route;
        }
    }

    /**
     * @inheritDoc
     */
    public function allOnHost(?string $host): Iterator
    {
        if (isset($host) && isset($this->hostMap[$host])) {
            foreach ($this->hostMap[$host] as $name) {
                yield $this->routes[$name];
            }
        }

        if (isset($this->hostMap[self::ANY])) {
            foreach ($this->hostMap[self::ANY] as $name) {
                yield $this->routes[$name];
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function has(string $name): bool
    {
        return isset($this->routes[$name]);
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): RouteInterface
    {
        if (!isset($this->routes[$name])) {
            throw new RouteNotFoundException(sprintf(
                'The collection does not contain a route with the name %s',
                $name
            ));
        }

        return $this->routes[$name];
    }

    /**
     * @inheritDoc
     */
    public function add(RouteInterface ...$routes): RouteCollectionInterface
    {
        foreach ($routes as $route) {
            $name = $route->getName();
            $host = $route->getHost() ?? self::ANY;

            if (isset($this->routes[$name])) {
                throw new RouteAlreadyExistsException(sprintf(
                    'The collection already contains a route with the name %s',
                    $name
                ));
            }

            $this->routes[$name] = $route;
            $this->hostMap[$host][] = $name;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setHost(string $host): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setHost($host);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setConsumedMediaTypes(string ...$mediaTypes): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setConsumedMediaTypes(...$mediaTypes);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setProducedMediaTypes(string ...$mediaTypes): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setProducedMediaTypes(...$mediaTypes);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $name, $value): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addPrefix(string $prefix): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addPrefix($prefix);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addSuffix(string $suffix): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addSuffix($suffix);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMethod(string ...$methods): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addMethod(...$methods);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addConsumedMediaType(string ...$mediaTypes): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addConsumedMediaType(...$mediaTypes);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addProducedMediaType(string ...$mediaTypes): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addProducedMediaType(...$mediaTypes);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addMiddleware(...$middlewares);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addPriorityMiddleware(MiddlewareInterface ...$middlewares): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addPriorityMiddleware(...$middlewares);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addTag(string ...$tags): RouteCollectionInterface
    {
        foreach ($this->routes as $route) {
            $route->addTag(...$tags);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->routes);
    }
}
