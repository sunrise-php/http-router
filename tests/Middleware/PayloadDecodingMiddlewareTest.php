<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\CodecManagerInterface;
use Sunrise\Http\Router\Dictionary\ErrorMessage;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\Exception\CodecException;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Middleware\PayloadDecodingMiddleware;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Tests\TestKit;

final class PayloadDecodingMiddlewareTest extends TestCase
{
    use TestKit;

    private CodecManagerInterface&MockObject $mockedCodecManager;
    private ServerRequestInterface&MockObject $mockedRequest;
    private StreamInterface&MockObject $mockedRequestBody;
    private RequestHandlerInterface&MockObject $mockedRequestHandler;
    private ResponseInterface&MockObject $mockedResponse;
    private RouteInterface&MockObject $mockedRoute;

    protected function setUp(): void
    {
        $this->mockedCodecManager = $this->createMock(CodecManagerInterface::class);
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRequestBody = $this->createMock(StreamInterface::class);
        $this->mockedRequest->method('getBody')->willReturn($this->mockedRequestBody);
        $this->mockedRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
        $this->mockedRoute = $this->createMock(RouteInterface::class);
    }

    public function testProcess(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn([MediaType::JSON]);
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('{"foo":"bar"}');
        $this->mockedCodecManager->expects(self::once())->method('decode')->with(self::anything(), '{"foo":"bar"}')->willReturn(['foo' => 'bar']);
        $this->mockedRequest->expects(self::once())->method('withParsedBody')->with(['foo' => 'bar'])->willReturnSelf();
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new PayloadDecodingMiddleware($this->mockedCodecManager))->process($this->mockedRequest, $this->mockedRequestHandler));
    }

    public function testCaseInsensitiveClientMediaType(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('APPLICATION/JSON');
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn([MediaType::JSON]);
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('{"foo":"bar"}');
        $this->mockedCodecManager->expects(self::once())->method('decode')->with(self::anything(), '{"foo":"bar"}')->willReturn(['foo' => 'bar']);
        $this->mockedRequest->expects(self::once())->method('withParsedBody')->with(['foo' => 'bar'])->willReturnSelf();
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new PayloadDecodingMiddleware($this->mockedCodecManager))->process($this->mockedRequest, $this->mockedRequestHandler));
    }

    public function testClientNotProducedMediaType(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('');
        $this->mockedCodecManager->expects(self::never())->method('decode');
        (new PayloadDecodingMiddleware($this->mockedCodecManager))->process($this->mockedRequest, $this->mockedRequestHandler);
    }

    public function testServerNotConsumedMediaType(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn([$this->mockMediaType('application/xml')]);
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedCodecManager->expects(self::never())->method('decode');
        (new PayloadDecodingMiddleware($this->mockedCodecManager))->process($this->mockedRequest, $this->mockedRequestHandler);
    }

    public function testCodecManagerNotSupportedMediaType(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn([MediaType::JSON]);
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->willReturn(false);
        $this->mockedCodecManager->expects(self::never())->method('decode');
        (new PayloadDecodingMiddleware($this->mockedCodecManager))->process($this->mockedRequest, $this->mockedRequestHandler);
    }

    public function testInvalidPayload(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn([MediaType::JSON]);
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('');
        $this->mockedCodecManager->expects(self::once())->method('decode')->willThrowException(new CodecException());
        $middleware = new PayloadDecodingMiddleware($this->mockedCodecManager);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::INVALID_BODY);

        try {
            $middleware->process($this->mockedRequest, $this->mockedRequestHandler);
        } catch (HttpException $e) {
            self::assertSame(400, $e->getCode());
            throw $e;
        }
    }

    public function testCodecContext(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn([MediaType::JSON]);
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('{}');
        $this->mockedCodecManager->expects(self::once())->method('decode')->with(self::anything(), self::anything(), ['foo' => 'bar'])->willReturn([]);
        $this->mockedRequest->expects(self::once())->method('withParsedBody')->with([])->willReturnSelf();
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedRequest);
        (new PayloadDecodingMiddleware($this->mockedCodecManager, codecContext: ['foo' => 'bar']))->process($this->mockedRequest, $this->mockedRequestHandler);
    }

    public function testErrorStatusCode(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn([MediaType::JSON]);
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('');
        $this->mockedCodecManager->expects(self::once())->method('decode')->willThrowException(new CodecException());
        $middleware = new PayloadDecodingMiddleware($this->mockedCodecManager, errorStatusCode: 500);
        $this->expectException(HttpException::class);

        try {
            $middleware->process($this->mockedRequest, $this->mockedRequestHandler);
        } catch (HttpException $e) {
            self::assertSame(500, $e->getCode());
            throw $e;
        }
    }

    public function testErrorMessage(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn([MediaType::JSON]);
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('');
        $this->mockedCodecManager->expects(self::once())->method('decode')->willThrowException(new CodecException());
        $middleware = new PayloadDecodingMiddleware($this->mockedCodecManager, errorMessage: 'foo');
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('foo');
        $middleware->process($this->mockedRequest, $this->mockedRequestHandler);
    }
}
