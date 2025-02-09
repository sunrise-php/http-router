<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\ResponseResolver;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionMethod;
use RuntimeException;
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\CodecManagerInterface;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\Exception\CodecException;
use Sunrise\Http\Router\MediaTypeInterface;
use Sunrise\Http\Router\ResponseResolver\EncodableResponseResolver;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Tests\TestKit;

final class EncodableResponseResolverTest extends TestCase
{
    use TestKit;

    private ResponseFactoryInterface&MockObject $mockedResponseFactory;
    private CodecManagerInterface&MockObject $mockedCodecManager;
    private MediaTypeInterface&MockObject $mockedMediaType;
    private ServerRequestInterface&MockObject $mockedRequest;
    private RouteInterface&MockObject $mockedRoute;
    private ResponseInterface&MockObject $mockedResponse;
    private StreamInterface&MockObject $mockedResponseBody;

    protected function setUp(): void
    {
        $this->mockedResponseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->mockedCodecManager = $this->createMock(CodecManagerInterface::class);
        $this->mockedMediaType = $this->createMock(MediaTypeInterface::class);
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRoute = $this->createMock(RouteInterface::class);
        $this->mockedRequest->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
        $this->mockedResponseBody = $this->createMock(StreamInterface::class);
    }

    public function testResolveResponse(): void
    {
        $responder = new ReflectionMethod(new class
        {
            #[EncodableResponse]
            public function test(): void
            {
            }
        }, 'test');

        $this->mockedMediaType->method('getIdentifier')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getProducedMediaTypes')->willReturn([$this->mockedMediaType]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Accept')->willReturn('application/json');
        $this->mockedCodecManager->expects(self::once())->method('encode')->with($this->mockedMediaType, ['foo'])->willReturn('["foo"]');
        $this->mockedResponse->expects(self::once())->method('withHeader')->with('Content-Type', 'application/json; charset=UTF-8')->willReturnSelf();
        $this->mockedResponse->expects(self::once())->method('getBody')->willReturn($this->mockedResponseBody);
        $this->mockedResponseBody->expects(self::once())->method('write')->with('["foo"]');
        $this->mockedResponseFactory->expects(self::once())->method('createResponse')->with(200)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new EncodableResponseResolver($this->mockedResponseFactory, $this->mockedCodecManager, $this->mockedMediaType))->resolveResponse(['foo'], $responder, $this->mockedRequest));
    }

    public function testUnsupportedResponse(): void
    {
        $responder = new ReflectionMethod(new class
        {
            public function test(): void
            {
            }
        }, 'test');

        $this->mockedCodecManager->expects(self::never())->method('encode');
        $this->mockedResponseFactory->expects(self::never())->method('createResponse');
        self::assertNull((new EncodableResponseResolver($this->mockedResponseFactory, $this->mockedCodecManager, $this->mockedMediaType))->resolveResponse(['foo'], $responder, $this->mockedRequest));
    }

    public function testInvalidResponse(): void
    {
        $responder = new ReflectionMethod(new class
        {
            #[EncodableResponse]
            public function test(): void
            {
            }
        }, 'test');

        $this->mockedMediaType->method('getIdentifier')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getProducedMediaTypes')->willReturn([$this->mockedMediaType]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Accept')->willReturn('application/json');
        $this->mockedCodecManager->expects(self::once())->method('encode')->with($this->mockedMediaType, ["\xff"])->willThrowException(new CodecException());
        $this->mockedResponseFactory->expects(self::never())->method('createResponse');
        $resolver = new EncodableResponseResolver($this->mockedResponseFactory, $this->mockedCodecManager, $this->mockedMediaType);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/responder ".*?" returned a response that could not be encoded/');
        $resolver->resolveResponse(["\xff"], $responder, $this->mockedRequest);
    }

    public function testDefaultMediaType(): void
    {
        $responder = new ReflectionMethod(new class
        {
            #[EncodableResponse]
            public function test(): void
            {
            }
        }, 'test');

        $this->mockedMediaType->expects(self::once())->method('getIdentifier')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getProducedMediaTypes')->willReturn([]);
        $this->mockedRequest->expects(self::never())->method('getHeaderLine');
        $this->mockedCodecManager->expects(self::once())->method('encode')->with($this->mockedMediaType, ['foo'])->willReturn('["foo"]');
        $this->mockedResponse->expects(self::once())->method('withHeader')->with('Content-Type', 'application/json; charset=UTF-8')->willReturnSelf();
        $this->mockedResponse->expects(self::once())->method('getBody')->willReturn($this->mockedResponseBody);
        $this->mockedResponseBody->expects(self::once())->method('write')->with('["foo"]');
        $this->mockedResponseFactory->expects(self::once())->method('createResponse')->with(200)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new EncodableResponseResolver($this->mockedResponseFactory, $this->mockedCodecManager, $this->mockedMediaType))->resolveResponse(['foo'], $responder, $this->mockedRequest));
    }

    public function testDefaultMediaTypeFromAnnotation(): void
    {
        $responder = new ReflectionMethod(new class
        {
            #[EncodableResponse(defaultMediaType: MediaType::JSON)]
            public function test(): void
            {
            }
        }, 'test');

        $this->mockedMediaType->expects(self::never())->method('getIdentifier');
        $this->mockedRoute->expects(self::once())->method('getProducedMediaTypes')->willReturn([]);
        $this->mockedRequest->expects(self::never())->method('getHeaderLine');
        $this->mockedCodecManager->expects(self::once())->method('encode')->with(MediaType::JSON, ['foo'])->willReturn('["foo"]');
        $this->mockedResponse->expects(self::once())->method('withHeader')->with('Content-Type', 'application/json; charset=UTF-8')->willReturnSelf();
        $this->mockedResponse->expects(self::once())->method('getBody')->willReturn($this->mockedResponseBody);
        $this->mockedResponseBody->expects(self::once())->method('write')->with('["foo"]');
        $this->mockedResponseFactory->expects(self::once())->method('createResponse')->with(200)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new EncodableResponseResolver($this->mockedResponseFactory, $this->mockedCodecManager, $this->mockedMediaType))->resolveResponse(['foo'], $responder, $this->mockedRequest));
    }

    public function testClientPreferredMediaType(): void
    {
        $responder = new ReflectionMethod(new class
        {
            #[EncodableResponse(defaultMediaType: MediaType::JSON)]
            public function test(): void
            {
            }
        }, 'test');

        $applicationJson = $this->mockMediaType('application/json');
        $applicationXml = $this->mockMediaType('application/xml');
        $applicationYaml = $this->mockMediaType('application/yaml');
        $this->mockedRoute->expects(self::once())->method('getProducedMediaTypes')->willReturn([$applicationXml, $applicationYaml, $applicationJson]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Accept')->willReturn('application/xml; q=0.25, application/json; q=0.75, application/yaml; q=0.5');
        $this->mockedCodecManager->expects(self::once())->method('encode')->with($applicationXml, ['foo'])->willReturn('["foo"]');
        $this->mockedResponse->expects(self::once())->method('withHeader')->with('Content-Type', 'application/json; charset=UTF-8')->willReturnSelf();
        $this->mockedResponse->expects(self::once())->method('getBody')->willReturn($this->mockedResponseBody);
        $this->mockedResponseBody->expects(self::once())->method('write')->with('["foo"]');
        $this->mockedResponseFactory->expects(self::once())->method('createResponse')->with(200)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new EncodableResponseResolver($this->mockedResponseFactory, $this->mockedCodecManager, $this->mockedMediaType))->resolveResponse(['foo'], $responder, $this->mockedRequest));
    }

    public function testCaseInsensitiveClientMediaType(): void
    {
        $responder = new ReflectionMethod(new class
        {
            #[EncodableResponse]
            public function test(): void
            {
            }
        }, 'test');

        $this->mockedMediaType->method('getIdentifier')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getProducedMediaTypes')->willReturn([$this->mockedMediaType]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Accept')->willReturn('APPLICATION/JSON');
        $this->mockedCodecManager->expects(self::once())->method('encode')->with($this->mockedMediaType, ['foo'])->willReturn('["foo"]');
        $this->mockedResponse->expects(self::once())->method('withHeader')->with('Content-Type', 'application/json; charset=UTF-8')->willReturnSelf();
        $this->mockedResponse->expects(self::once())->method('getBody')->willReturn($this->mockedResponseBody);
        $this->mockedResponseBody->expects(self::once())->method('write')->with('["foo"]');
        $this->mockedResponseFactory->expects(self::once())->method('createResponse')->with(200)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new EncodableResponseResolver($this->mockedResponseFactory, $this->mockedCodecManager, $this->mockedMediaType))->resolveResponse(['foo'], $responder, $this->mockedRequest));
    }

    public function testCodecContext(): void
    {
        $responder = new ReflectionMethod(new class
        {
            #[EncodableResponse(codecContext: ['foo' => 'baz', 'baz' => 'qux'])]
            public function test(): void
            {
            }
        }, 'test');

        $this->mockedMediaType->method('getIdentifier')->willReturn('application/json');
        $this->mockedRoute->expects(self::once())->method('getProducedMediaTypes')->willReturn([$this->mockedMediaType]);
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Accept')->willReturn('application/json');
        $this->mockedCodecManager->expects(self::once())->method('encode')->with($this->mockedMediaType, ['foo'], ['foo' => 'baz', 'baz' => 'qux', 'bar' => 'baz'])->willReturn('["foo"]');
        $this->mockedResponse->expects(self::once())->method('withHeader')->with('Content-Type', 'application/json; charset=UTF-8')->willReturnSelf();
        $this->mockedResponse->expects(self::once())->method('getBody')->willReturn($this->mockedResponseBody);
        $this->mockedResponseBody->expects(self::once())->method('write')->with('["foo"]');
        $this->mockedResponseFactory->expects(self::once())->method('createResponse')->with(200)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, (new EncodableResponseResolver($this->mockedResponseFactory, $this->mockedCodecManager, $this->mockedMediaType, codecContext: ['foo' => 'bar', 'bar' => 'baz']))->resolveResponse(['foo'], $responder, $this->mockedRequest));
    }

    public function testWeight(): void
    {
        self::assertSame(10, (new EncodableResponseResolver($this->mockedResponseFactory, $this->mockedCodecManager, $this->mockedMediaType))->getWeight());
    }
}
