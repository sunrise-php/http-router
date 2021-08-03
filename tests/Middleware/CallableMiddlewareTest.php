<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Middleware;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use Sunrise\Http\Router\Tests\Fixture;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

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
        $middleware = new CallableMiddleware(function ($request, $handler) {
            return $handler->handle($request);
        });

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    /**
     * @return void
     */
    public function testRun() : void
    {
        $middleware = new CallableMiddleware(function ($request, $handler) {
            return $handler->handle($request);
        });

        $request = (new ServerRequestFactory)->createServerRequest('GET', '/');
        $requestHandler = new Fixture\BlankRequestHandler();
        $middleware->process($request, $requestHandler);

        $this->assertTrue($requestHandler->isRunned());
    }
}
