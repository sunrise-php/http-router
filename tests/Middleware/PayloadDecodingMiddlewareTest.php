<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Middleware;

use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Coder\CodecManagerInterface;
use Sunrise\Coder\Exception\CodecException;
use Sunrise\Http\Router\Dictionary\ErrorMessage;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Middleware\PayloadDecodingMiddleware;
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
        $this->mockedRequest->expects(self::any())->method('getBody')->willReturn($this->mockedRequestBody);
        $this->mockedRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
        $this->mockedRoute = $this->createMock(RouteInterface::class);
    }

    public function testProcess(): void
    {
        $this->mockedRequest->expects(self::exactly(2))->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedRoute->expects(self::exactly(2))->method('getConsumedMediaTypes')->willReturn([$this->mockMediaType('application/json')]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->withAnyParameters()->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('{"foo":"bar"}');
        $this->mockedCodecManager->expects(self::once())->method('decode')->with(self::anything(), '{"foo":"bar"}')->willReturn(['foo' => 'bar']);
        $this->mockedRequest->expects(self::once())->method('withParsedBody')->with(['foo' => 'bar'])->willReturnSelf();
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new PayloadDecodingMiddleware($this->mockedCodecManager))->process($this->mockedRequest, $this->mockedRequestHandler));
    }

    public function testCaseInsensitiveServerConsumedMediaType(): void
    {
        $this->mockedRequest->expects(self::exactly(2))->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedRoute->expects(self::exactly(2))->method('getConsumedMediaTypes')->willReturn([$this->mockMediaType('APPLICATION/JSON')]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->withAnyParameters()->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('{"foo":"bar"}');
        $this->mockedCodecManager->expects(self::once())->method('decode')->with(self::anything(), '{"foo":"bar"}')->willReturn(['foo' => 'bar']);
        $this->mockedRequest->expects(self::once())->method('withParsedBody')->with(['foo' => 'bar'])->willReturnSelf();
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new PayloadDecodingMiddleware($this->mockedCodecManager))->process($this->mockedRequest, $this->mockedRequestHandler));
    }

    public function testCaseInsensitiveClientProducedMediaType(): void
    {
        $this->mockedRequest->expects(self::exactly(2))->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedRoute->expects(self::exactly(2))->method('getConsumedMediaTypes')->willReturn([$this->mockMediaType('application/json')]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('APPLICATION/JSON');
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->withAnyParameters()->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('{"foo":"bar"}');
        $this->mockedCodecManager->expects(self::once())->method('decode')->with(self::anything(), '{"foo":"bar"}')->willReturn(['foo' => 'bar']);
        $this->mockedRequest->expects(self::once())->method('withParsedBody')->with(['foo' => 'bar'])->willReturnSelf();
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new PayloadDecodingMiddleware($this->mockedCodecManager))->process($this->mockedRequest, $this->mockedRequestHandler));
    }

    public function testServerConsumesNothing(): void
    {
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn([]);
        $this->mockedRequest->expects(self::never())->method('getHeaderLine');
        $this->mockedCodecManager->expects(self::never())->method('supportsMediaType');
        $this->mockedRequestBody->expects(self::never())->method('__toString');
        $this->mockedCodecManager->expects(self::never())->method('decode');
        $this->mockedRequest->expects(self::never())->method('withParsedBody');
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new PayloadDecodingMiddleware($this->mockedCodecManager))->process($this->mockedRequest, $this->mockedRequestHandler));
    }

    public function testClientProducesNothing(): void
    {
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn([$this->mockMediaType('application/json')]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('');
        $this->mockedCodecManager->expects(self::never())->method('supportsMediaType');
        $this->mockedRequestBody->expects(self::never())->method('__toString');
        $this->mockedCodecManager->expects(self::never())->method('decode');
        $this->mockedRequest->expects(self::never())->method('withParsedBody');
        $this->mockedRequestHandler->expects(self::never())->method('handle');
        $middleware = new PayloadDecodingMiddleware($this->mockedCodecManager);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::MISSING_MEDIA_TYPE);

        try {
            $middleware->process($this->mockedRequest, $this->mockedRequestHandler);
        } catch (HttpException $e) {
            self::assertSame(400, $e->getCode());
            throw $e;
        }
    }

    public function testUnsupportedMediaTypeByRoute(): void
    {
        $this->mockedRequest->expects(self::exactly(2))->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedRoute->expects(self::exactly(2))->method('getConsumedMediaTypes')->willReturn([$this->mockMediaType('application/json')]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/xml');
        $this->mockedCodecManager->expects(self::never())->method('supportsMediaType');
        $this->mockedRequestBody->expects(self::never())->method('__toString');
        $this->mockedCodecManager->expects(self::never())->method('decode');
        $this->mockedRequest->expects(self::never())->method('withParsedBody');
        $this->mockedRequestHandler->expects(self::never())->method('handle');
        $middleware = new PayloadDecodingMiddleware($this->mockedCodecManager);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::UNSUPPORTED_MEDIA_TYPE);

        try {
            $middleware->process($this->mockedRequest, $this->mockedRequestHandler);
        } catch (HttpException $e) {
            self::assertSame(415, $e->getCode());
            self::assertContains(['Accept', 'application/json'], $e->getHeaderFields());
            throw $e;
        }
    }

    public function testUnsupportedMediaTypeByCodecManager(): void
    {
        $this->mockedRequest->expects(self::exactly(2))->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedRoute->expects(self::exactly(2))->method('getConsumedMediaTypes')->willReturn([$this->mockMediaType('application/json')]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->withAnyParameters()->willReturn(false);
        $this->mockedRoute->expects(self::once())->method('getName')->willReturn('foo');
        $this->mockedRequestBody->expects(self::never())->method('__toString');
        $this->mockedCodecManager->expects(self::never())->method('decode');
        $this->mockedRequest->expects(self::never())->method('withParsedBody');
        $this->mockedRequestHandler->expects(self::never())->method('handle');
        $middleware = new PayloadDecodingMiddleware($this->mockedCodecManager);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The route "foo" expects the media type "application/json" that is not supported by the codec manager.');
        $middleware->process($this->mockedRequest, $this->mockedRequestHandler);
    }

    public function testInvalidBody(): void
    {
        $this->mockedRequest->expects(self::exactly(2))->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedRoute->expects(self::exactly(2))->method('getConsumedMediaTypes')->willReturn([$this->mockMediaType('application/json')]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->withAnyParameters()->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('!');
        $this->mockedCodecManager->expects(self::once())->method('decode')->with(self::anything(), '!')->willThrowException(new CodecException());
        $this->mockedRequest->expects(self::never())->method('withParsedBody');
        $this->mockedRequestHandler->expects(self::never())->method('handle');
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
        $this->mockedRequest->expects(self::exactly(2))->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedRoute->expects(self::exactly(2))->method('getConsumedMediaTypes')->willReturn([$this->mockMediaType('application/json')]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $this->mockedCodecManager->expects(self::once())->method('supportsMediaType')->withAnyParameters()->willReturn(true);
        $this->mockedRequestBody->expects(self::once())->method('__toString')->willReturn('{"foo":"bar"}');
        $this->mockedCodecManager->expects(self::once())->method('decode')->with(self::anything(), '{"foo":"bar"}', ['foo' => 'bar'])->willReturn(['foo' => 'bar']);
        $this->mockedRequest->expects(self::once())->method('withParsedBody')->with(['foo' => 'bar'])->willReturnSelf();
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new PayloadDecodingMiddleware($this->mockedCodecManager, codecContext: ['foo' => 'bar']))->process($this->mockedRequest, $this->mockedRequestHandler));
    }
}
