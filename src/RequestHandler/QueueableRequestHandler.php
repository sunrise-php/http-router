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

namespace Sunrise\Http\Router\RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;

/**
 * @since 2.0.0
 *
 * @extends SplQueue<MiddlewareInterface>
 */
final class QueueableRequestHandler extends SplQueue implements RequestHandlerInterface
{
    public function __construct(
        private readonly RequestHandlerInterface $endpoint,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->isEmpty()
            ? $this->endpoint->handle($request)
            : ($clone = clone $this)->dequeue()->process($request, $clone);
    }
}
