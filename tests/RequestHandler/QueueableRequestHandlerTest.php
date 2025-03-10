<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\RequestHandler;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler\QueueableRequestHandler;
use PHPUnit\Framework\TestCase;

final class QueueableRequestHandlerTest extends TestCase
{
    private ServerRequestInterface&MockObject $mockedRequest;
    private ResponseInterface&MockObject $mockedResponse;
    private RequestHandlerInterface&MockObject $mockedEndpoint;

    protected function setUp(): void
    {
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
        $this->mockedEndpoint = $this->createMock(RequestHandlerInterface::class);
    }

    public function testHandle(): void
    {
        $requestHandler = new QueueableRequestHandler($this->mockedEndpoint);
        self::assertCount(0, $requestHandler);

        $callableMiddleware = function (ServerRequestInterface $request, QueueableRequestHandler $handler): ResponseInterface {
            self::assertSame($this->mockedRequest, $request);
            return $handler->handle($request);
        };

        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects(self::once())->method('process')->with($this->mockedRequest, $requestHandler)->willReturnCallback($callableMiddleware);
        $requestHandler->enqueue($middleware);
        self::assertCount(1, $requestHandler);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects(self::once())->method('process')->with($this->mockedRequest, $requestHandler)->willReturnCallback($callableMiddleware);
        $requestHandler->enqueue($middleware);
        self::assertCount(2, $requestHandler);

        $this->mockedEndpoint->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, $requestHandler->handle($this->mockedRequest));

        // The request handler is immutable.
        self::assertCount(2, $requestHandler);
    }

    public function testHandleWithChainBreakingMiddleware(): void
    {
        $requestHandler = new QueueableRequestHandler($this->mockedEndpoint);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects(self::once())->method('process')->with($this->mockedRequest, $requestHandler)->willReturn($this->mockedResponse);
        $requestHandler->enqueue($middleware);

        $this->mockedEndpoint->expects(self::never())->method('handle');
        self::assertSame($this->mockedResponse, $requestHandler->handle($this->mockedRequest));
    }

    public function testHandleWithoutMiddlewares(): void
    {
        $requestHandler = new QueueableRequestHandler($this->mockedEndpoint);
        $this->mockedEndpoint->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, $requestHandler->handle($this->mockedRequest));
    }
}
