<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Middleware\CallbackMiddleware;
use Sunrise\Http\Router\Tests\Fixtures;

/**
 * CallableMiddlewareTest
 */
class CallableMiddlewareTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $callback = new Fixtures\Middlewares\BlankMiddleware();
        $middleware = new CallbackMiddleware($callback);

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    /**
     * @return void
     */
    public function testRun() : void
    {
        $callback = new Fixtures\Middlewares\BlankMiddleware();
        $middleware = new CallbackMiddleware($callback);

        $this->assertSame($callback, $middleware->getCallback());

        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = new Fixtures\Controllers\BlankController();
        $middleware->process($request, $requestHandler);

        $this->assertTrue($callback->isRunned());
    }
}
