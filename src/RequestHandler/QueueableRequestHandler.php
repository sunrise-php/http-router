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
 * @extends SplQueue<MiddlewareInterface>
 */
final class QueueableRequestHandler extends SplQueue implements RequestHandlerInterface
{
    /**
     * @param array<array-key, MiddlewareInterface> $middlewares
     */
    public function __construct(
        private readonly RequestHandlerInterface $requestHandler,
        array $middlewares,
    ) {
        foreach ($middlewares as $middleware) {
            $this->enqueue($middleware);
        }
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ! $this->isEmpty() ?
            ($clone = clone $this)->dequeue()->process($request, $clone) :
            $this->requestHandler->handle($request);
    }
}
