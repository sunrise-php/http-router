<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Middleware;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use Sunrise\Http\Router\Test\Fixture;

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
        $middleware = new CallableMiddleware(function () {
        });

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    /**
     * @return void
     */
    public function testRun() : void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $callback = new Fixture\Middlewares\BlankMiddleware();
        $middleware = new CallableMiddleware($callback);
        $requestHandler = new Fixture\Controllers\BlankController();
        $middleware->process($request, $requestHandler);
        $this->assertTrue($callback->isRunned());
        $this->assertTrue($requestHandler->isRunned());
    }
}
