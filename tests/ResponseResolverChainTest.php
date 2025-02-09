<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\ResponseHeader;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\ResponseResolverChain;
use Sunrise\Http\Router\ResponseResolverInterface;

final class ResponseResolverChainTest extends TestCase
{
    private ServerRequestInterface&MockObject $mockedRequest;

    protected function setUp(): void
    {
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
    }

    public function testResolveObject(): void
    {
        $responder = new ReflectionMethod(new class
        {
            public function test(): void
            {
            }
        }, 'test');

        $expectedResponse = $this->createMock(ResponseInterface::class);

        $responseResolver = $this->createMock(ResponseResolverInterface::class);
        $responseResolver->expects(self::never())->method('resolveResponse');

        self::assertSame($expectedResponse, (new ResponseResolverChain([$responseResolver]))->resolveResponse($expectedResponse, $responder, $this->mockedRequest));
    }

    public function testResolveResponse(): void
    {
        $responder = new ReflectionMethod(new class
        {
            public function test(): void
            {
            }
        }, 'test');

        $expectedResponse = $this->createMock(ResponseInterface::class);

        /** @var list<ResponseResolverInterface&MockObject> $responseResolvers */
        $responseResolvers = [
            $this->createMock(ResponseResolverInterface::class),
            $this->createMock(ResponseResolverInterface::class),
            $this->createMock(ResponseResolverInterface::class),
            $this->createMock(ResponseResolverInterface::class),
        ];

        $responseResolvers[1]->expects(self::once())->method('resolveResponse')->with(null, $responder, $this->mockedRequest)->willReturn($expectedResponse);
        $responseResolvers[1]->method('getWeight')->willReturn(1);
        $responseResolvers[2]->expects(self::once())->method('resolveResponse')->with(null, $responder, $this->mockedRequest)->willReturn(null);
        $responseResolvers[2]->method('getWeight')->willReturn(3);
        $responseResolvers[3]->expects(self::never())->method('resolveResponse');
        $responseResolvers[3]->method('getWeight')->willReturn(0);
        $responseResolvers[0]->expects(self::once())->method('resolveResponse')->with(null, $responder, $this->mockedRequest)->willReturn(null);
        $responseResolvers[0]->method('getWeight')->willReturn(2);

        self::assertSame($expectedResponse, (new ResponseResolverChain($responseResolvers))->resolveResponse(null, $responder, $this->mockedRequest));
    }

    public function testCompleteResponse(): void
    {
        $responder = new ReflectionMethod(new class
        {
            #[ResponseStatus(204, 'No Content')]
            #[ResponseHeader('x-foo', 'foo')]
            #[ResponseHeader('x-bar', 'bar')]
            public function test(): void
            {
            }
        }, 'test');

        $expectedResponse = $this->createMock(ResponseInterface::class);
        $expectedResponse->expects(self::once())->method('withStatus')->with(204, 'No Content')->willReturnSelf();
        $expectedResponse->expects(self::exactly(2))->method('withHeader')->willReturnCallback(
            static function ($name, $value) use ($expectedResponse) {
                self::assertContains([$name, $value], [['x-foo', 'foo'], ['x-bar', 'bar']]);
                return $expectedResponse;
            }
        );

        $responseResolver = $this->createMock(ResponseResolverInterface::class);
        $responseResolver->expects(self::once())->method('resolveResponse')->with(null, $responder, $this->mockedRequest)->willReturn($expectedResponse);

        self::assertSame($expectedResponse, (new ResponseResolverChain([$responseResolver]))->resolveResponse(null, $responder, $this->mockedRequest));
    }

    public function testUnsupportedResponse(): void
    {
        $responder = new ReflectionMethod(new class
        {
            public function test(): void
            {
            }
        }, 'test');

        $responseResolver = $this->createMock(ResponseResolverInterface::class);
        $responseResolver->expects(self::once())->method('resolveResponse')->with(null, $responder, $this->mockedRequest)->willReturn(null);

        $responseResolverChain = new ResponseResolverChain([$responseResolver]);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/returned an unsupported response/');
        $responseResolverChain->resolveResponse(null, $responder, $this->mockedRequest);
    }

    public function testNoResolvers(): void
    {
        $responder = new ReflectionMethod(new class
        {
            public function test(): void
            {
            }
        }, 'test');

        $responseResolverChain = new ResponseResolverChain([]);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/returned an unsupported response/');
        $responseResolverChain->resolveResponse(null, $responder, $this->mockedRequest);
    }
}
