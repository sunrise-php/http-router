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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;

use function rtrim;
use function strtoupper;

/**
 * Route
 *
 * Use the {@see RouteFactory} factory to create this class.
 */
class Route implements RouteInterface
{

    /**
     * The route name
     *
     * @var string
     */
    private string $name;

    /**
     * The route path
     *
     * @var string
     */
    private string $path;

    /**
     * The route methods
     *
     * @var list<string>
     */
    private array $methods = [];

    /**
     * The route's consumes media types
     *
     * @var list<MediaType>
     */
    private array $consumesMediaTypes = [];

    /**
     * The route's produces media types
     *
     * @var list<MediaType>
     */
    private array $producesMediaTypes = [];

    /**
     * The route request handler
     *
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $requestHandler;

    /**
     * The route middlewares
     *
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * The route attributes
     *
     * @var array<string, mixed>
     */
    private array $attributes = [];

    /**
     * The route summary
     *
     * @var string
     */
    private string $summary = '';

    /**
     * The route description
     *
     * @var string
     */
    private string $description = '';

    /**
     * The route tags
     *
     * @var list<string>
     */
    private array $tags = [];

    /**
     * Constructor of the class
     *
     * @param string $name
     * @param string $path
     * @param list<string> $methods
     * @param RequestHandlerInterface $requestHandler
     * @param list<MiddlewareInterface> $middlewares
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        string $name,
        string $path,
        array $methods,
        RequestHandlerInterface $requestHandler,
        array $middlewares = [],
        array $attributes = []
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->setMethods(...$methods);
        $this->requestHandler = $requestHandler;
        $this->setMiddlewares(...$middlewares);
        $this->attributes = $attributes;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @inheritDoc
     */
    public function getConsumesMediaTypes(): array
    {
        return $this->consumesMediaTypes;
    }

    /**
     * @inheritDoc
     */
    public function getProducesMediaTypes(): array
    {
        return $this->producesMediaTypes;
    }

    /**
     * @inheritDoc
     */
    public function getRequestHandler(): RequestHandlerInterface
    {
        return $this->requestHandler;
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): RouteInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPath(string $path): RouteInterface
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMethods(string ...$methods): RouteInterface
    {
        $this->methods = [];
        foreach ($methods as $method) {
            $this->methods[] = strtoupper($method);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setConsumesMediaTypes(MediaType ...$mediaTypes): RouteInterface
    {
        $this->consumesMediaTypes = [];
        foreach ($mediaTypes as $mediaType) {
            $this->consumesMediaTypes[] = $mediaType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setProducesMediaTypes(MediaType ...$mediaTypes): RouteInterface
    {
        $this->producesMediaTypes = [];
        foreach ($mediaTypes as $mediaType) {
            $this->producesMediaTypes[] = $mediaType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRequestHandler(RequestHandlerInterface $requestHandler): RouteInterface
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMiddlewares(MiddlewareInterface ...$middlewares): RouteInterface
    {
        $this->middlewares = [];
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAttributes(array $attributes): RouteInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $name, $value): RouteInterface
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSummary(string $summary): RouteInterface
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description): RouteInterface
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTags(string ...$tags): RouteInterface
    {
        $this->tags = [];
        foreach ($tags as $tag) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addPrefix(string $prefix): RouteInterface
    {
        // https://github.com/sunrise-php/http-router/issues/26
        $prefix = rtrim($prefix, '/');

        $this->path = $prefix . $this->path;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addSuffix(string $suffix): RouteInterface
    {
        $this->path .= $suffix;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMethod(string ...$methods): RouteInterface
    {
        foreach ($methods as $method) {
            $this->methods[] = strtoupper($method);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addConsumesMediaType(MediaType ...$mediaTypes): RouteInterface
    {
        foreach ($mediaTypes as $mediaType) {
            $this->consumesMediaTypes[] = $mediaType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addProducesMediaType(MediaType ...$mediaTypes): RouteInterface
    {
        foreach ($mediaTypes as $mediaType) {
            $this->producesMediaTypes[] = $mediaType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares): RouteInterface
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addPriorityMiddleware(MiddlewareInterface ...$middlewares): RouteInterface
    {
        $newValue = [];

        foreach ($middlewares as $middleware) {
            $newValue[] = $middleware;
        }

        foreach ($this->middlewares as $middleware) {
            $newValue[] = $middleware;
        }

        $this->middlewares = $newValue;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addTag(string ...$tags): RouteInterface
    {
        foreach ($tags as $tag) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedAttributes(array $attributes): RouteInterface
    {
        $clone = clone $this;

        /** @psalm-suppress MixedAssignment */
        foreach ($attributes as $key => $value) {
            $clone->attributes[$key] = $value;
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $request = $request->withAttribute(self::ATTR_ROUTE, $this);

        /** @psalm-suppress MixedAssignment */
        foreach ($this->attributes as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        if (empty($this->middlewares)) {
            return $this->requestHandler->handle($request);
        }

        return (new QueueableRequestHandler($this->requestHandler, ...$this->middlewares))->handle($request);
    }
}
