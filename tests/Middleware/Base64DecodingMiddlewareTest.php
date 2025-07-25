<?php

declare(strict_types=1);

namespace Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Middleware\Base64DecodingMiddleware;
use PHPUnit\Framework\TestCase;

final class Base64DecodingMiddlewareTest extends TestCase
{
    private ServerRequestInterface&MockObject $mockedRequest;
    private RequestHandlerInterface&MockObject $mockedRequestHandler;
    private ResponseInterface&MockObject $mockedResponse;
    private StreamInterface&MockObject $mockedStream;
    private StreamFactoryInterface&MockObject $mockedStreamFactory;
    /** @var resource */
    private $resource;

    protected function setUp(): void
    {
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
        $this->mockedStream = $this->createMock(StreamInterface::class);
        $this->mockedStreamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->resource = \fopen('php://temp', 'r+b');
    }

    protected function tearDown(): void
    {
        if (\is_resource($this->resource)) {
            \fclose($this->resource);
        }
    }

    public function testProcess(): void
    {
        $middleware = new Base64DecodingMiddleware($this->mockedStreamFactory);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Encoding')->willReturn('base64');
        $this->mockedRequest->expects(self::once())->method('getBody')->willReturn($this->mockedStream);
        $this->mockedStream->expects(self::once())->method('detach')->willReturn($this->resource);
        $this->mockedStreamFactory->expects(self::once())->method('createStreamFromResource')->with($this->resource)->willReturn($this->mockedStream);
        $this->mockedRequest->expects(self::once())->method('withBody')->with($this->mockedStream)->willReturnSelf();
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        \fwrite($this->resource, 'Zm9v');
        \rewind($this->resource);
        $actualResponse = $middleware->process($this->mockedRequest, $this->mockedRequestHandler);
        self::assertSame($this->mockedResponse, $actualResponse);
        self::assertSame('foo', \fread($this->resource, 4));
    }
}
