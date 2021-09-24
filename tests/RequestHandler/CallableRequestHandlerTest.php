<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\RequestHandler;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\Tests\Fixture;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

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
        $requestHandler = new CallableRequestHandler(function () {
        });

        $this->assertInstanceOf(RequestHandlerInterface::class, $requestHandler);
    }

    /**
     * @return void
     */
    public function testRun() : void
    {
        $callback = new Fixture\BlankRequestHandler();
        $requestHandler = new CallableRequestHandler($callback);

        $request = (new ServerRequestFactory)->createServerRequest('GET', '/');
        $requestHandler->handle($request);

        $this->assertTrue($callback->isRunned());
    }
}
