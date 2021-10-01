<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\RequestHandler;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\Test\Fixture;

/**
 * CallableRequestHandlerTest
 */
class CallableRequestHandlerTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $callback = new Fixture\Controllers\BlankController();
        $requestHandler = new CallableRequestHandler($callback);

        $this->assertInstanceOf(RequestHandlerInterface::class, $requestHandler);
    }

    /**
     * @return void
     */
    public function testRun() : void
    {
        $callback = new Fixture\Controllers\BlankController();
        $requestHandler = new CallableRequestHandler($callback);

        $this->assertSame($callback, $requestHandler->getCallback());

        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler->handle($request);

        $this->assertTrue($callback->isRunned());
    }
}
