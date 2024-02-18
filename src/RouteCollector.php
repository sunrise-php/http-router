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

use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Loader\LoaderInterface;

/**
 * RouteCollector
 */
class RouteCollector implements LoaderInterface
{
    /**
     * @var list<RouteInterface>
     */
    private array $routes = [];

    /**
     * @return list<RouteInterface>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Makes a new route with the given parameters
     *
     * @param string $name
     * @param string $path
     * @param list<string> $methods
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function route(
        string $name,
        string $path,
        array $methods,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        $route = new Route(
            $name,
            $path,
            $methods,
            $requestHandler,
            $middlewares,
            $attributes,
        );

        $this->routes[] = $route;

        return $route;
    }

    /**
     * Makes a new route that will respond to HEAD requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function head(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_HEAD],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to GET requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function get(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_GET],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to POST requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function post(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_POST],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to PUT requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function put(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_PUT],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to PATCH requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function patch(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_PATCH],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to DELETE requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function delete(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_DELETE],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    /**
     * Makes a new route that will respond to PURGE requests
     *
     * @param string $name
     * @param string $path
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     *
     * @return RouteInterface
     */
    public function purge(
        string $name,
        string $path,
        mixed $requestHandler,
        array $middlewares = [],
        array $attributes = [],
    ) : RouteInterface {
        return $this->route(
            $name,
            $path,
            [RequestMethodInterface::METHOD_PURGE],
            $requestHandler,
            $middlewares,
            $attributes,
        );
    }

    public function group(callable $callback, array $middlewares = []): static
    {
        $collector = new self();

        $callback($collector);

        $collector->addPriorityMiddleware(...$middlewares);

        foreach ($collector->routes as $route) {
            $this->routes[] = $route;
        }

        return $collector;
    }

    public function addPrefix(string $prefix): static
    {
        foreach ($this->routes as $route) {
            $route->addPrefix($prefix);
        }

        return $this;
    }

    public function addSuffix(string $suffix): static
    {
        foreach ($this->routes as $route) {
            $route->addSuffix($suffix);
        }

        return $this;
    }

    public function addMethod(string ...$methods): static
    {
        foreach ($this->routes as $route) {
            $route->addMethod(...$methods);
        }

        return $this;
    }

    public function addConsumesMediaType(MediaType ...$mediaTypes): static
    {
        foreach ($this->routes as $route) {
            $route->addConsumedMediaType(...$mediaTypes);
        }

        return $this;
    }

    public function addProducesMediaType(MediaType ...$mediaTypes): static
    {
        foreach ($this->routes as $route) {
            $route->addProducedMediaType(...$mediaTypes);
        }

        return $this;
    }

    public function addPriorityMiddleware(MiddlewareInterface ...$middlewares): static
    {
        foreach ($this->routes as $route) {
            $route->setMiddlewares(...$middlewares, ...$route->getMiddlewares());
        }

        return $this;
    }

    public function addMiddleware(MiddlewareInterface ...$middlewares): static
    {
        foreach ($this->routes as $route) {
            $route->addMiddleware(...$middlewares);
        }

        return $this;
    }

    public function addTag(string ...$tags): static
    {
        foreach ($this->routes as $route) {
            $route->addTag(...$tags);
        }

        return $this;
    }

    public function setDeprecation(bool $isDeprecated): static
    {
        foreach ($this->routes as $route) {
            $route->setDeprecation($isDeprecated);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function load(): iterable
    {
        return $this->routes;
    }
}
