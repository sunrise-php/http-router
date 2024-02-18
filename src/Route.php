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
     * The route's consumed media types
     *
     * @var list<string>
     */
    private array $consumedMediaTypes = [];

    /**
     * The route's produced media types
     *
     * @var list<string>
     */
    private array $producedMediaTypes = [];

    /**
     * The route's request handler
     *
     * @var mixed
     */
    private mixed $requestHandler;

    /**
     * The route middlewares
     *
     * @var list<mixed>
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

    private array $constraints = [];

    private ?string $pattern = null;

    /**
     * Constructor of the class
     *
     * @param string $name
     * @param string $path
     * @param list<string> $methods
     * @param mixed $requestHandler
     * @param list<mixed> $middlewares
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        string $name,
        string $path,
        array $methods,
        mixed $requestHandler,
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
    public function getConsumedMediaTypes(): array
    {
        return $this->consumedMediaTypes;
    }

    /**
     * @inheritDoc
     */
    public function getProducedMediaTypes(): array
    {
        return $this->producedMediaTypes;
    }

    /**
     * @inheritDoc
     */
    public function getRequestHandler(): mixed
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
    public function setConsumesMediaTypes(string ...$mediaTypes): static
    {
        $this->consumedMediaTypes = [];
        foreach ($mediaTypes as $mediaType) {
            $this->consumedMediaTypes[] = $mediaType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setProducesMediaTypes(string ...$mediaTypes): static
    {
        $this->producedMediaTypes = [];
        foreach ($mediaTypes as $mediaType) {
            $this->producedMediaTypes[] = $mediaType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRequestHandler(mixed $requestHandler): static
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMiddlewares(mixed ...$middlewares): static
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
    public function addConsumedMediaType(string ...$mediaTypes): static
    {
        foreach ($mediaTypes as $mediaType) {
            $this->consumedMediaTypes[] = $mediaType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addProducedMediaType(string ...$mediaTypes): static
    {
        foreach ($mediaTypes as $mediaType) {
            $this->producedMediaTypes[] = $mediaType;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMiddleware(mixed ...$middlewares): static
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

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function setConstraints(array $constrains): static
    {
        $this->constraints = $constrains;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(?string $pattern): static
    {
        $this->pattern = $pattern;

        return $this;
    }
}
