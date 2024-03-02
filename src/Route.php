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

use function array_key_exists;
use function in_array;

final class Route
{
    public function __construct(
        private readonly string $name,
        private readonly string $path,
        private readonly mixed $requestHandler,
        /** @var array<string, string> */
        private readonly array $patterns = [],
        /** @var list<string> */
        private readonly array $methods = [],
        /** @var array<string, mixed> */
        private array $attributes = [],
        /** @var list<mixed> */
        private readonly array $middlewares = [],
        /** @var list<mixed> */
        private readonly array $constraints = [],
        /** @var list<MediaTypeInterface> */
        private readonly array $consumes = [],
        /** @var list<MediaTypeInterface> */
        private readonly array $produces = [],
        private readonly string $summary = '',
        private readonly string $description = '',
        /** @var list<string> */
        private readonly array $tags = [],
        private readonly bool $isDeprecated = false,
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
     * @return array<string, string>
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * @return list<string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function supportsMethod(string $method): bool
    {
        if ($this->methods === []) {
            return true;
        }

        return in_array($method, $this->methods, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function withAttributes(array $attributes): self
    {
        $clone = clone $this;
        $clone->attributes = $attributes;

        return $clone;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function withAddedAttributes(array $attributes): self
    {
        $clone = clone $this;
        foreach ($attributes as $name => $value) {
            $clone->attributes[$name] = $value;
        }

        return $clone;
    }

    /**
     * @return list<mixed>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @return list<mixed>
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * @return list<MediaTypeInterface>
     */
    public function getConsumedMediaTypes(): array
    {
        return $this->consumes;
    }

    /**
     * @return list<MediaTypeInterface>
     */
    public function getProducedMediaTypes(): array
    {
        return $this->produces;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return list<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function isDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * @return non-empty-string|null
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }
}
