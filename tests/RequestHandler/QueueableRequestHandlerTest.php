<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\RequestHandler;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use Sunrise\Http\Router\Tests\Fixture;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * QueueableRequestHandlerTest
 */
class QueueableRequestHandlerTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $endpoint = new Fixture\BlankRequestHandler();
        $requestHandler = new QueueableRequestHandler($endpoint);

        $this->assertInstanceOf(RequestHandlerInterface::class, $requestHandler);
    }

    /**
     * @return void
     */
    public function testHandle() : void
    {
        $endpoint = new Fixture\BlankRequestHandler();
        $requestHandler = new QueueableRequestHandler($endpoint);

        $request = (new ServerRequestFactory)->createServerRequest('GET', '/');
        $requestHandler->handle($request);

        $this->assertTrue($endpoint->isRunned());
    }

    /**
     * @return void
     */
    public function testHandleWithMiddlewares() : void
    {
        $middlewares = [
            new Fixture\BlankMiddleware(),
            new Fixture\BlankMiddleware(),
            new Fixture\BlankMiddleware(),
        ];

        $endpoint = new Fixture\BlankRequestHandler();
        $requestHandler = new QueueableRequestHandler($endpoint);
        $requestHandler->add(...$middlewares);

        $request = (new ServerRequestFactory)->createServerRequest('GET', '/');
        $requestHandler->handle($request);

        $this->assertTrue($middlewares[0]->isRunned());
        $this->assertTrue($middlewares[1]->isRunned());
        $this->assertTrue($middlewares[2]->isRunned());
        $this->assertTrue($endpoint->isRunned());
    }

    /**
     * @return void
     */
    public function testHandleWithBrokenMiddleware() : void
    {
        $middlewares = [
            new Fixture\BlankMiddleware(),
            new Fixture\BlankMiddleware(true),
            new Fixture\BlankMiddleware(),
        ];

        $endpoint = new Fixture\BlankRequestHandler();
        $requestHandler = new QueueableRequestHandler($endpoint);
        $requestHandler->add(...$middlewares);

        $request = (new ServerRequestFactory)->createServerRequest('GET', '/');
        $requestHandler->handle($request);

        $this->assertTrue($middlewares[0]->isRunned());
        $this->assertTrue($middlewares[1]->isRunned());
        $this->assertFalse($middlewares[2]->isRunned());
        $this->assertFalse($endpoint->isRunned());
    }
}
