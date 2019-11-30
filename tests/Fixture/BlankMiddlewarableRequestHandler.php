<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;

/**
 * BlankMiddlewarableRequestHandler
 */
class BlankMiddlewarableRequestHandler implements MiddlewareInterface, RequestHandlerInterface
{

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $handler->handle($request);
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return (new ResponseFactory)->createResponse();
    }
}
