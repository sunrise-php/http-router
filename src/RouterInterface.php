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

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;

/**
 * @since 3.0.0
 */
interface RouterInterface extends RequestHandlerInterface
{
    /**
     * @return array<string, RouteInterface>
     *
     * @since 1.0.0
     */
    public function getRoutes(): array;

    /**
     * @throws InvalidArgumentException
     *
     * @since 2.0.0
     */
    public function getRoute(string $name): RouteInterface;

    /**
     * @since 2.16.0
     */
    public function hasRoute(string $name): bool;

    /**
     * @since 1.0.0
     *
     * @throws InvalidArgumentException
     */
    public function addRoute(RouteInterface ...$routes): void;

    /**
     * @throws HttpExceptionInterface
     *
     * @since 1.0.0
     */
    public function match(ServerRequestInterface $request): RouteInterface;

    /**
     * @inheritDoc
     *
     * @throws HttpExceptionInterface
     *
     * @since 1.0.0
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;

    /**
     * @throws InvalidArgumentException
     *
     * @since 3.0.0
     */
    public function runRoute(RouteInterface|string $route, ServerRequestInterface $request): ResponseInterface;

    /**
     * @param array<string, mixed> $values
     *
     * @throws InvalidArgumentException
     *
     * @since 3.0.0
     */
    public function buildRoute(RouteInterface|string $route, array $values = []): string;

    /**
     * @return non-empty-string
     *
     * @throws InvalidArgumentException
     *
     * @since 3.0.0
     */
    public function compileRoute(RouteInterface|string $route): string;

    /**
     * @throws InvalidArgumentException
     *
     * @since 3.0.0
     */
    public function getRouteRequestHandler(RouteInterface|string $route): RequestHandlerInterface;
}
