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
use Sunrise\Http\Router\Exception\HttpException;

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
    public function buildRoute(RouteInterface|string $route, array $values = [], bool $strictly = false): string;

    /**
     * @throws HttpException
     *
     * @since 1.0.0
     */
    public function match(ServerRequestInterface $request): RouteInterface;
}
