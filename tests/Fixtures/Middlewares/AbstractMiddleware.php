<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;

abstract class AbstractMiddleware implements MiddlewareInterface
{

    /**
     * Indicates if the middleware is breakable
     *
     * @var bool
     */
    private $isBreakable;

    /**
     * Indicates if the middleware was runned
     *
     * @var bool
     */
    private $isRunned = false;

    /**
     * The request that was handled by the called method
     *
     * @var ServerRequestInterface|null
     */
    private $request = null;

    /**
     * Constructor of the class
     *
     * @param bool $isBreakable
     */
    public function __construct(bool $isBreakable = false)
    {
        $this->isBreakable = $isBreakable;
    }

    /**
     * Checks if the middleware was runned
     *
     * @return bool
     */
    public function isRunned() : bool
    {
        return $this->isRunned;
    }

    /**
     * Gets the request that was handled by the called method
     *
     * @return ServerRequestInterface|null
     */
    public function getRequest() : ?ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->isRunned = true;
        $this->request = $request;

        if ($this->isBreakable) {
            return (new ResponseFactory)->createResponse(200);
        }

        return $handler->handle($request);
    }
}
