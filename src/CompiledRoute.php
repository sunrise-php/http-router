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

/**
 * Compiled route
 *
 * @since 3.0.0
 */
final class CompiledRoute implements RouteInterface
{

    /**
     * Constructor of the class
     *
     * @param RouteInterface $route
     * @param string $regex
     */
    public function __construct(
        private RouteInterface $route,
        private string $regex,
    ) {
    }

    /**
     * Gets the route's regular expression
     *
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->route->getName();
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->route->getPath();
    }

    /**
     * @inheritDoc
     */
    public function getMethods(): array
    {
        return $this->route->getMethods();
    }

    /**
     * @inheritDoc
     */
    public function getConsumesMediaTypes(): array
    {
        return $this->route->getConsumesMediaTypes();
    }

    /**
     * @inheritDoc
     */
    public function getProducesMediaTypes(): array
    {
        return $this->route->getProducesMediaTypes();
    }

    /**
     * @inheritDoc
     */
    public function getRequestHandler(): RequestHandlerInterface
    {
        return $this->route->getRequestHandler();
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewares(): array
    {
        return $this->route->getMiddlewares();
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->route->getAttributes();
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->route->getAttribute($name, $default);
    }

    /**
     * @inheritDoc
     */
    public function getSummary(): ?string
    {
        return $this->route->getSummary();
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
    {
        return $this->route->getDescription();
    }

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return $this->route->getTags();
    }

    /**
     * @inheritDoc
     */
    public function isDeprecated(): bool
    {
        return $this->route->isDeprecated();
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): static
    {
        $this->route->setName($name);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPath(string $path): static
    {
        $this->route->setPath($path);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMethods(string ...$methods): static
    {
        $this->route->setMethods(...$methods);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setConsumesMediaTypes(MediaType ...$mediaTypes): static
    {
        $this->route->setConsumesMediaTypes(...$mediaTypes);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setProducesMediaTypes(MediaType ...$mediaTypes): static
    {
        $this->route->setProducesMediaTypes(...$mediaTypes);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRequestHandler(RequestHandlerInterface $requestHandler): static
    {
        $this->route->setRequestHandler($requestHandler);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMiddlewares(MiddlewareInterface ...$middlewares): static
    {
        $this->route->setMiddlewares(...$middlewares);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAttributes(array $attributes): static
    {
        $this->route->setAttributes($attributes);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->route->setAttribute($name, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSummary(?string $summary): static
    {
        $this->route->setSummary($summary);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDescription(?string $description): static
    {
        $this->route->setDescription($description);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTags(string ...$tags): static
    {
        $this->route->setTags(...$tags);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDeprecation(bool $isDeprecated): static
    {
        $this->route->setDeprecation($isDeprecated);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addPrefix(string $prefix): static
    {
        $this->route->addPrefix($prefix);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addSuffix(string $suffix): static
    {
        $this->route->addSuffix($suffix);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMethod(string ...$methods): static
    {
        $this->route->addMethod(...$methods);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addConsumesMediaType(MediaType ...$mediaTypes): static
    {
        $this->route->addConsumesMediaType(...$mediaTypes);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addProducesMediaType(MediaType ...$mediaTypes): static
    {
        $this->route->addProducesMediaType(...$mediaTypes);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMiddleware(MiddlewareInterface ...$middlewares): static
    {
        $this->route->addMiddleware(...$middlewares);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addTag(string ...$tags): static
    {
        $this->route->addTag(...$tags);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedAttributes(array $attributes): static
    {
        $clone = clone $this;
        $clone->route = $this->route->withAddedAttributes($attributes);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->route->handle($request);
    }
}
