<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\RequestHandler;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Sunrise\Http\Router\Tests\Fixtures;

/**
 * QueueableRequestHandlerTest
 */
class QueueableRequestHandlerTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $endpoint = new Fixtures\Controllers\BlankController();
        $requestHandler = new QueueableRequestHandler($endpoint);

        $this->assertInstanceOf(RequestHandlerInterface::class, $requestHandler);
    }

    /**
     * @return void
     */
    public function testRun() : void
    {
        $endpoint = new Fixtures\Controllers\BlankController();
        $requestHandler = new QueueableRequestHandler($endpoint);

        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler->handle($request);

        $this->assertTrue($endpoint->isRunned());
    }

    /**
     * @return void
     */
    public function testRunWithMiddlewares() : void
    {
        $middlewares = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $endpoint = new Fixtures\Controllers\BlankController();
        $requestHandler = new QueueableRequestHandler($endpoint);
        $requestHandler->add(...$middlewares);

        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler->handle($request);

        $this->assertTrue($middlewares[0]->isRunned());
        $this->assertTrue($middlewares[1]->isRunned());
        $this->assertTrue($middlewares[2]->isRunned());
        $this->assertTrue($endpoint->isRunned());
    }

    /**
     * @return void
     */
    public function testRunWithBrokenMiddleware() : void
    {
        $middlewares = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(true),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $endpoint = new Fixtures\Controllers\BlankController();
        $requestHandler = new QueueableRequestHandler($endpoint);
        $requestHandler->add(...$middlewares);

        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler->handle($request);

        $this->assertTrue($middlewares[0]->isRunned());
        $this->assertTrue($middlewares[1]->isRunned());
        $this->assertFalse($middlewares[2]->isRunned());
        $this->assertFalse($endpoint->isRunned());
    }
}
