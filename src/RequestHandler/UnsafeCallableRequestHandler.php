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
use Psr\Http\Server\RequestHandlerInterface;

/**
 * UnsafeCallableRequestHandler
 *
 * @since 3.0.0
 *
 * @template T as callable(ServerRequestInterface=): ResponseInterface
 */
final class UnsafeCallableRequestHandler implements RequestHandlerInterface
{

    /**
     * The request handler's callback
     *
     * @var T
     */
    private $callback;

    /**
     * Constructor of the class
     *
     * @param T $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->callback)($request);
    }
}