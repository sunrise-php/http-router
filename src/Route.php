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

use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;

use function in_array;

/**
 * @since 1.0.0
 */
final class Route implements RouteInterface
{
    public function __construct(
        private readonly string $name,
        private readonly string $path,
        private readonly mixed $requestHandler,
        /** @var array<string, string> */
        private readonly array $patterns = [],
        /** @var array<array-key, string> */
        private readonly array $methods = [],
        /** @var array<string, mixed> */
        private array $attributes = [],
        /** @var array<array-key, mixed> */
        private readonly array $middlewares = [],
        /** @var array<array-key, MediaTypeInterface> */
        private readonly array $consumes = [],
        /** @var array<array-key, MediaTypeInterface> */
        private readonly array $produces = [],
        /** @var array<array-key, string> */
        private readonly array $tags = [],
        private readonly string $summary = '',
        private readonly string $description = '',
        private readonly bool $isDeprecated = false,
        private readonly bool $isApiOperation = false,
        /** @var array<array-key, mixed>|object|null */
        private readonly array|object|null $apiOperationFields = null,
        /** @var non-empty-string|null */
        private readonly ?string $pattern = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRequestHandler(): mixed
    {
        return $this->requestHandler;
    }

    /**
     * @inheritDoc
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * @inheritDoc
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function allowsMethod(string $method): bool
    {
        return $this->methods === [] || in_array($method, $this->methods, true);
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function withAddedAttributes(array $attributes): static
    {
        $clone = clone $this;
        foreach ($attributes as $name => $value) {
            $clone->attributes[$name] = $value;
        }

        return $clone;
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
    public function getConsumedMediaTypes(): array
    {
        return $this->consumes;
    }

    /**
     * @inheritDoc
     */
    public function getProducedMediaTypes(): array
    {
        return $this->produces;
    }

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * @inheritDoc
     */
    public function isApiOperation(): bool
    {
        return $this->isApiOperation;
    }

    /**
     * @inheritDoc
     */
    public function getApiOperationFields(): array|object|null
    {
        return $this->apiOperationFields;
    }

    /**
     * @inheritDoc
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }
}
