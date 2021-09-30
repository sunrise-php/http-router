<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\RequestHandler;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Sunrise\Http\Router\Test\Fixture;

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
        $endpoint = new Fixture\Controllers\BlankController();
        $requestHandler = new QueueableRequestHandler($endpoint);

        $this->assertInstanceOf(RequestHandlerInterface::class, $requestHandler);
    }

    /**
     * @return void
     */
    public function testRun() : void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $endpoint = new Fixture\Controllers\BlankController();
        $requestHandler = new QueueableRequestHandler($endpoint);
        $requestHandler->handle($request);
        $this->assertTrue($endpoint->isRunned());
    }

    /**
     * @return void
     */
    public function testRunWithMiddlewares() : void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $endpoint = new Fixture\Controllers\BlankController();
        $requestHandler = new QueueableRequestHandler($endpoint);

        $middlewares = [
            new Fixture\Middlewares\BlankMiddleware(),
            new Fixture\Middlewares\BlankMiddleware(),
            new Fixture\Middlewares\BlankMiddleware(),
        ];

        $requestHandler->add(...$middlewares);

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
        $request = $this->createMock(ServerRequestInterface::class);
        $endpoint = new Fixture\Controllers\BlankController();
        $requestHandler = new QueueableRequestHandler($endpoint);

        $middlewares = [
            new Fixture\Middlewares\BlankMiddleware(),
            new Fixture\Middlewares\BlankMiddleware(true),
            new Fixture\Middlewares\BlankMiddleware(),
        ];

        $requestHandler->add(...$middlewares);

        $requestHandler->handle($request);
        $this->assertTrue($middlewares[0]->isRunned());
        $this->assertTrue($middlewares[1]->isRunned());
        $this->assertFalse($middlewares[2]->isRunned());
        $this->assertFalse($endpoint->isRunned());
    }
}
