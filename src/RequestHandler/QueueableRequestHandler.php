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
 * QueueableRequestHandler
 */
final class QueueableRequestHandler implements RequestHandlerInterface
{

    /**
     * The request handler's middleware queue
     *
     * @var SplQueue<MiddlewareInterface>
     */
    private SplQueue $middlewareQueue;

    /**
     * The request handler's request handler
     *
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $requestHandler;

    /**
     * Constructor of the class
     *
     * @param RequestHandlerInterface $requestHandler
     */
    public function __construct(RequestHandlerInterface $requestHandler)
    {
        /** @var SplQueue<MiddlewareInterface> */
        $this->middlewareQueue = new SplQueue();

        $this->requestHandler = $requestHandler;
    }

    /**
     * Adds the given middleware(s) to the request handler's middleware queue
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return void
     */
    public function add(MiddlewareInterface ...$middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $this->middlewareQueue->enqueue($middleware);
        }
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->middlewareQueue->isEmpty()) {
            return $this->middlewareQueue->dequeue()->process($request, $this);
        }

        return $this->requestHandler->handle($request);
    }
}
