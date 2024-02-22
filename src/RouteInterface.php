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

    /**
     * The server request's attribute name for a route instance
     *
     * @var string
     *
     * @since 2.11.0
     */
    public const ATTR_ROUTE = '@route';

    public function getConstraints(): array;

    public function setConstraints(array $constrains): static;

    /**
     * Gets the route name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Gets the route path
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Gets the route methods
     *
     * @return list<string>
     */
    public function getMethods(): array;

    /**
     * Gets the route's consumed media types
     *
     * @return list<MediaTypeInterface>
     *
     * @since 3.0.0
     */
    public function getConsumedMediaTypes(): array;

    /**
     * Gets the route's produced media types
     *
     * @return list<MediaTypeInterface>
     *
     * @since 3.0.0
     */
    public function getProducedMediaTypes(): array;

    /**
     * Gets the route's request handler
     *
     * @return mixed
     */
    public function getRequestHandler(): mixed;

    /**
     * Gets the route middlewares
     *
     * @return list<mixed>
     */
    public function getMiddlewares(): array;

    /**
     * Gets the route attributes
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array;

    /**
     * Gets the route's attribute value by its given name
     * or returns the given default value if the parameter doesn't exist
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute(string $name, mixed $default = null): mixed;

    /**
     * Gets the route summary
     *
     * @return string|null
     *
     * @since 2.4.0
     */
    public function getSummary(): ?string;

    /**
     * Gets the route description
     *
     * @return string|null
     *
     * @since 2.4.0
     */
    public function getDescription(): ?string;

    /**
     * Gets the route tags
     *
     * @return list<string>
     *
     * @since 2.4.0
     */
    public function getTags(): array;

    /**
     * Checks if the route is deprecated
     *
     * @return bool
     *
     * @since 3.0.0
     */
    public function isDeprecated(): bool;

    /**
     * Sets the given name to the route
     *
     * @param string $name
     *
     * @return static
     */
    public function setName(string $name): static;

    /**
     * Sets the given path to the route
     *
     * @param string $path
     *
     * @return static
     */
    public function setPath(string $path): static;

    /**
     * Sets the given method(s) to the route
     *
     * @param string ...$methods
     *
     * @return static
     */
    public function setMethods(string ...$methods): static;

    /**
     * Sets the given consumed media type(s) to the route
     *
     * @param MediaTypeInterface ...$mediaTypes
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function setConsumesMediaTypes(MediaTypeInterface ...$mediaTypes): static;

    /**
     * Sets the given produced media type(s) to the route
     *
     * @param MediaTypeInterface ...$mediaTypes
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function setProducesMediaTypes(MediaTypeInterface ...$mediaTypes): static;

    /**
     * Sets the given request handler to the route
     *
     * @param mixed $requestHandler
     *
     * @return static
     */
    public function setRequestHandler(mixed $requestHandler): static;

    /**
     * Sets the given middleware(s) to the route
     *
     * @param mixed ...$middlewares
     *
     * @return static
     */
    public function setMiddlewares(mixed ...$middlewares): static;

    /**
     * Sets the given attributes to the route
     *
     * @param array<string, mixed> $attributes
     *
     * @return static
     */
    public function setAttributes(array $attributes): static;

    /**
     * Sets the given attribute to the route
     *
     * @param string $name
     * @param mixed $value
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function setAttribute(string $name, mixed $value): static;

    /**
     * Sets the given summary to the route
     *
     * @param string|null $summary
     *
     * @return static
     *
     * @since 2.4.0
     */
    public function setSummary(?string $summary): static;

    /**
     * Sets the given description to the route
     *
     * @param string|null $description
     *
     * @return static
     *
     * @since 2.4.0
     */
    public function setDescription(?string $description): static;

    /**
     * Sets the given tag(s) to the route
     *
     * @param string ...$tags
     *
     * @return static
     *
     * @since 2.4.0
     */
    public function setTags(string ...$tags): static;

    /**
     * Sets the route's deprecation
     *
     * @param bool $isDeprecated
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function setDeprecation(bool $isDeprecated): static;

    /**
     * Adds the given prefix to the route path
     *
     * @param string $prefix
     *
     * @return static
     */
    public function addPrefix(string $prefix): static;

    /**
     * Adds the given suffix to the route path
     *
     * @param string $suffix
     *
     * @return static
     */
    public function addSuffix(string $suffix): static;

    /**
     * Adds the given method(s) to the route
     *
     * @param string ...$methods
     *
     * @return static
     */
    public function addMethod(string ...$methods): static;

    /**
     * Adds the given consumed media type(s) to the route
     *
     * @param MediaTypeInterface ...$mediaTypes
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function addConsumedMediaType(MediaTypeInterface ...$mediaTypes): static;

    /**
     * Adds the given produced media type(s) to the route
     *
     * @param MediaTypeInterface ...$mediaTypes
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function addProducedMediaType(MediaTypeInterface ...$mediaTypes): static;

    /**
     * Adds the given middleware(s) to the route
     *
     * @param mixed ...$middlewares
     *
     * @return static
     */
    public function addMiddleware(mixed ...$middlewares): static;

    /**
     * Adds the given tag(s) to the route
     *
     * @param string ...$tags
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function addTag(string ...$tags): static;

    /**
     * Returns the route's clone with the given attributes
     *
     * This method MUST NOT change the object state.
     *
     * @param array<string, mixed> $attributes
     *
     * @return static
     */
    public function withAddedAttributes(array $attributes): static;
}
