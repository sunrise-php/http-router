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
     * The route's request handler
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
     * @var string|null
     */
    private ?string $summary = null;

    /**
     * The route description
     *
     * @var string|null
     */
    private ?string $description = null;

    /**
     * The route tags
     *
     * @var list<string>
     */
    private array $tags = [];

    /**
     * The route's deprecation sign
     *
     * @var bool
     */
    private bool $isDeprecated = false;

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
        array $attributes = [],
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
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
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
    public function isDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMethods(string ...$methods): static
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
    public function setConsumesMediaTypes(MediaType ...$mediaTypes): static
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
    public function setProducesMediaTypes(MediaType ...$mediaTypes): static
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
    public function setRequestHandler(RequestHandlerInterface $requestHandler): static
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMiddlewares(MiddlewareInterface ...$middlewares): static
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
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTags(string ...$tags): static
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
    public function setDeprecation(bool $isDeprecated): static
    {
        $this->isDeprecated = $isDeprecated;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addPrefix(string $prefix): static
    {
        // https://github.com/sunrise-php/http-router/issues/26
        $prefix = rtrim($prefix, '/');

        $this->path = $prefix . $this->path;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addSuffix(string $suffix): static
    {
        $this->path .= $suffix;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMethod(string ...$methods): static
    {
        foreach ($methods as $method) {
            $this->methods[] = strtoupper($method);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addConsumesMediaType(MediaType ...$mediaTypes): static
    {
        foreach ($mediaTypes as $mediaType) {
            $this->consumesMediaTypes[] = $mediaType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addProducesMediaType(MediaType ...$mediaTypes): static
    {
        foreach ($mediaTypes as $mediaType) {
            $this->producesMediaTypes[] = $mediaType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares): static
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addTag(string ...$tags): static
    {
        foreach ($tags as $tag) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedAttributes(array $attributes): static
    {
        $clone = clone $this;

        /** @psalm-suppress MixedAssignment */
        foreach ($attributes as $name => $value) {
            $clone->attributes[$name] = $value;
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
        foreach ($this->attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        if (empty($this->middlewares)) {
            return $this->requestHandler->handle($request);
        }

        return (new QueueableRequestHandler(
            $this->requestHandler,
            ...$this->middlewares,
        ))->handle($request);
    }
}
