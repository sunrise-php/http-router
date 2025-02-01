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

/**
 * @since 1.0.0
 */
interface RouteInterface
{
    /**
     * @since 2.0.0
     */
    public function getName(): string;

    public function getPath(): string;

    /**
     * @since 2.0.0
     */
    public function getRequestHandler(): mixed;

    /**
     * @return array<string, string>
     *
     * @since 3.0.0
     */
    public function getPatterns(): array;

    /**
     * @return array<array-key, string>
     */
    public function getMethods(): array;

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array;

    /**
     * @since 3.0.0
     */
    public function hasAttribute(string $name): bool;

    /**
     * @since 3.0.0
     */
    public function getAttribute(string $name, mixed $default = null): mixed;

    /**
     * @param array<string, mixed> $attributes
     *
     * @since 2.0.0
     */
    public function withAddedAttributes(array $attributes): static;

    /**
     * @return array<array-key, mixed>
     *
     * @since 2.0.0
     */
    public function getMiddlewares(): array;

    /**
     * @return array<array-key, MediaTypeInterface>
     *
     * @since 3.0.0
     */
    public function getConsumedMediaTypes(): array;

    /**
     * @return array<array-key, MediaTypeInterface>
     *
     * @since 3.0.0
     */
    public function getProducedMediaTypes(): array;

    /**
     * @return array<array-key, string>
     *
     * @since 2.4.0
     */
    public function getTags(): array;

    /**
     * @since 2.4.0
     */
    public function getSummary(): string;

    /**
     * @since 2.4.0
     */
    public function getDescription(): string;

    /**
     * @since 3.0.0
     */
    public function isDeprecated(): bool;

    /**
     * @since 3.0.0
     */
    public function isApiRoute(): bool;

    /**
     * @return non-empty-string|null
     *
     * @since 3.0.0
     */
    public function getPattern(): ?string;
}
