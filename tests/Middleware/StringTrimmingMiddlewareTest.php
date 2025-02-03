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
    private ServerRequestInterface&MockObject $mockedServerRequest;
    private RequestHandlerInterface&MockObject $mockedRequestHandler;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    public function testProcess(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn([[' foo ']]);
        $this->mockedServerRequest->expects(self::once())->method('withQueryParams')->with([['foo']])->willReturn($this->mockedServerRequest);
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn([[' bar ']]);
        $this->mockedServerRequest->expects(self::once())->method('withParsedBody')->with([['bar']])->willReturn($this->mockedServerRequest);
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedServerRequest)->willReturn($this->mockedResponse);
        $this->assertSame($this->mockedResponse, (new StringTrimmingMiddleware())->process($this->mockedServerRequest, $this->mockedRequestHandler));
    }

    public function testEmptyArrays(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn([]);
        $this->mockedServerRequest->expects(self::never())->method('withQueryParams');
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn([]);
        $this->mockedServerRequest->expects(self::never())->method('withParsedBody');
        (new StringTrimmingMiddleware())->process($this->mockedServerRequest, $this->mockedRequestHandler);
    }

    public function testNonArrayParsedBody(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn([]);
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn((object) ['foo']);
        $this->mockedServerRequest->expects(self::never())->method('withParsedBody');
        (new StringTrimmingMiddleware())->process($this->mockedServerRequest, $this->mockedRequestHandler);
    }

    public function testCustomTrimmer(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn(['foo']);
        $this->mockedServerRequest->expects(self::once())->method('withQueryParams')->with(['xxx'])->willReturn($this->mockedServerRequest);
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn(['bar']);
        $this->mockedServerRequest->expects(self::once())->method('withParsedBody')->with(['xxx'])->willReturn($this->mockedServerRequest);
        (new StringTrimmingMiddleware(static fn(): string => 'xxx'))->process($this->mockedServerRequest, $this->mockedRequestHandler);
    }
}
