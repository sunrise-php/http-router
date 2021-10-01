<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\RequestHandler;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CallableRequestHandler
 */
class CallableRequestHandler implements RequestHandlerInterface
{

    /**
     * The request handler callback
     *
     * @var callable
     */
    private $callback;

    /**
     * Constructor of the class
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Gets the request handler callback
     *
     * @return callable
     *
     * @since 2.10.0
     */
    public function getCallback() : callable
    {
        return $this->callback;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return ($this->callback)($request);
    }
}
