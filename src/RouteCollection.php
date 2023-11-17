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

use Generator;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Entity\MediaType;
use Traversable;

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
     * @inheritDoc
     */
    public function all(): Generator
    {
        foreach ($this->routes as $route) {
            yield $route;
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
    public function get(string $name): ?RouteInterface
    {
        return $this->routes[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function add(RouteInterface ...$routes): static
    {
        foreach ($routes as $route) {
            $this->routes[$route->getName()] = $route;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addPrefix(string $prefix): static
    {
        foreach ($this->routes as $route) {
            $route->addPrefix($prefix);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addSuffix(string $suffix): static
    {
        foreach ($this->routes as $route) {
            $route->addSuffix($suffix);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMethod(string ...$methods): static
    {
        foreach ($this->routes as $route) {
            $route->addMethod(...$methods);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addConsumesMediaType(MediaType ...$mediaTypes): static
    {
        foreach ($this->routes as $route) {
            $route->addConsumesMediaType(...$mediaTypes);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addProducesMediaType(MediaType ...$mediaTypes): static
    {
        foreach ($this->routes as $route) {
            $route->addProducesMediaType(...$mediaTypes);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addPriorityMiddleware(MiddlewareInterface ...$middlewares): static
    {
        foreach ($this->routes as $route) {
            $route->setMiddlewares(...$middlewares, ...$route->getMiddlewares());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares): static
    {
        foreach ($this->routes as $route) {
            $route->addMiddleware(...$middlewares);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addTag(string ...$tags): static
    {
        foreach ($this->routes as $route) {
            $route->addTag(...$tags);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDeprecation(bool $isDeprecated): static
    {
        foreach ($this->routes as $route) {
            $route->setDeprecation($isDeprecated);
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @since 3.0.0
     */
    public function count(): int
    {
        return count($this->routes);
    }

    /**
     * @inheritDoc
     *
     * @since 3.0.0
     */
    public function getIterator(): Traversable
    {
        yield from $this->all();
    }
}
