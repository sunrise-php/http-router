<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\RequestHandler;

/**
 * Import classes
 */
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
     * The request handler queue
     *
     * @var SplQueue<MiddlewareInterface>
     */
    private SplQueue $queue;

    /**
     * The request handler endpoint
     *
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
        /** @var SplQueue<MiddlewareInterface> */
        $queue = new SplQueue();

        $this->queue = $queue;
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
