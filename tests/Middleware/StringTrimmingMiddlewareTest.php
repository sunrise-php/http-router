<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Middleware\StringTrimmingMiddleware;
use PHPUnit\Framework\TestCase;

final class StringTrimmingMiddlewareTest extends TestCase
{
    private ServerRequestInterface&MockObject $mockedRequest;
    private RequestHandlerInterface&MockObject $mockedRequestHandler;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    public function testProcess(): void
    {
        $this->mockedRequest->expects(self::once())->method('getQueryParams')->willReturn([[' foo ']]);
        $this->mockedRequest->expects(self::once())->method('withQueryParams')->with([['foo']])->willReturnSelf();
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn([[' bar ']]);
        $this->mockedRequest->expects(self::once())->method('withParsedBody')->with([['bar']])->willReturnSelf();
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        $this->assertSame($this->mockedResponse, (new StringTrimmingMiddleware())->process($this->mockedRequest, $this->mockedRequestHandler));
    }

    public function testEmptyArrays(): void
    {
        $this->mockedRequest->expects(self::once())->method('getQueryParams')->willReturn([]);
        $this->mockedRequest->expects(self::never())->method('withQueryParams');
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn([]);
        $this->mockedRequest->expects(self::never())->method('withParsedBody');
        (new StringTrimmingMiddleware())->process($this->mockedRequest, $this->mockedRequestHandler);
    }

    public function testNonArrayParsedBody(): void
    {
        $this->mockedRequest->expects(self::once())->method('getQueryParams')->willReturn([]);
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn((object) ['foo']);
        $this->mockedRequest->expects(self::never())->method('withParsedBody');
        (new StringTrimmingMiddleware())->process($this->mockedRequest, $this->mockedRequestHandler);
    }

    public function testCustomTrimmer(): void
    {
        $this->mockedRequest->expects(self::once())->method('getQueryParams')->willReturn(['foo']);
        $this->mockedRequest->expects(self::once())->method('withQueryParams')->with(['xxx'])->willReturnSelf();
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn(['bar']);
        $this->mockedRequest->expects(self::once())->method('withParsedBody')->with(['xxx'])->willReturnSelf();
        (new StringTrimmingMiddleware(static fn(): string => 'xxx'))->process($this->mockedRequest, $this->mockedRequestHandler);
    }
}
