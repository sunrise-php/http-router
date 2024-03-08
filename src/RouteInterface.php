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

interface RouteInterface
{
    public function getName(): string;

    public function getPath(): string;

    public function getRequestHandler(): mixed;

    /**
     * @return array<string, string>
     */
    public function getPatterns(): array;

    /**
     * @return array<array-key, string>
     */
    public function getMethods(): array;

    public function allowsMethod(string $method): bool;

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array;

    public function getAttribute(string $name, mixed $default = null): mixed;

    public function hasAttribute(string $name): bool;

    /**
     * @param array<string, mixed> $attributes
     */
    public function withAttributes(array $attributes): static;

    /**
     * @param array<string, mixed> $attributes
     */
    public function withAddedAttributes(array $attributes): static;

    /**
     * @return array<array-key, mixed>
     */
    public function getMiddlewares(): array;

    /**
     * @return array<array-key, mixed>
     */
    public function getConstraints(): array;

    /**
     * @return array<array-key, MediaTypeInterface>
     */
    public function getConsumedMediaTypes(): array;

    /**
     * @return array<array-key, MediaTypeInterface>
     */
    public function getProducedMediaTypes(): array;

    /**
     * @return array<array-key, string>
     */
    public function getTags(): array;

    public function getSummary(): string;

    public function getDescription(): string;

    public function isDeprecated(): bool;

    /**
     * @return non-empty-string|null
     */
    public function getPattern(): ?string;
}
