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
     * @var SplQueue<MiddlewareInterface>
     */
    private SplQueue $queue;

    /**
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $endpoint;

    /**
     * Constructor of the class
     *
     * @param RequestHandlerInterface $endpoint
     */
    public function __construct(RequestHandlerInterface $endpoint)
    {
        $this->queue = new SplQueue();
        $this->endpoint = $endpoint;
    }

    /**
     * Adds the given middleware(s) to the request handler queue
     *
     * @param MiddlewareInterface ...$middlewares
     *
     * @return void
     */
    public function add(MiddlewareInterface ...$middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $this->queue->enqueue($middleware);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->queue->isEmpty()) {
            return $this->queue->dequeue()->process($request, $this);
        }

        return $this->endpoint->handle($request);
    }
}
