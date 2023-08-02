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

use Generator;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ReferenceResolverInterface
 *
 * @since 2.10.0
 */
interface ReferenceResolverInterface
{

    /**
     * Resolves the given reference to a request handler
     *
     * @param mixed $reference
     *
     * @return RequestHandlerInterface
     *
     * @since 3.0.0
     */
    public function resolveRequestHandler(mixed $reference): RequestHandlerInterface;

    /**
     * Resolves the given reference to a middleware
     *
     * @param mixed $reference
     *
     * @return MiddlewareInterface
     *
     * @since 3.0.0
     */
    public function resolveMiddleware(mixed $reference): MiddlewareInterface;

    /**
     * Resolves the given references to middlewares
     *
     * @param array<array-key, mixed> $references
     *
     * @return Generator<int, MiddlewareInterface>
     *
     * @since 3.0.0
     */
    public function resolveMiddlewares(array $references): Generator;
}
