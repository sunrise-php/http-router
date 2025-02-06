<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionParameter;
use Sunrise\Http\Router\ClassResolverInterface;
use Sunrise\Http\Router\MiddlewareResolver;
use Sunrise\Http\Router\ParameterResolverChainInterface;
use Sunrise\Http\Router\ResponseResolverChainInterface;

final class MiddlewareResolverTest extends TestCase
{
    private ClassResolverInterface&MockObject $mockedClassResolver;
    private ParameterResolverChainInterface&MockObject $mockedParameterResolverChain;
    private ResponseResolverChainInterface&MockObject $mockedResponseResolverChain;
    private ServerRequestInterface&MockObject $mockedServerRequest;
    private StreamInterface&MockObject $mockedServerRequestBody;
    private RequestHandlerInterface&MockObject $mockedServerRequestHandler;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedClassResolver = $this->createMock(ClassResolverInterface::class);
        $this->mockedParameterResolverChain = $this->createMock(ParameterResolverChainInterface::class);
        $this->mockedResponseResolverChain = $this->createMock(ResponseResolverChainInterface::class);
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedServerRequestBody = $this->createMock(StreamInterface::class);
        $this->mockedServerRequest->method('getBody')->willReturn($this->mockedServerRequestBody);
        $this->mockedServerRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    private function createResolver(): MiddlewareResolver
    {
        return new MiddlewareResolver(
            classResolver: $this->mockedClassResolver,
            parameterResolverChain: $this->mockedParameterResolverChain,
            responseResolverChain: $this->mockedResponseResolverChain,
        );
    }

    public function testResolveObject(): void
    {
        $expectedObject = $this->createMock(MiddlewareInterface::class);
        $this->assertSame($expectedObject, $this->createResolver()->resolveMiddleware($expectedObject));
    }

    public function testResolveClassName(): void
    {
        $expectedObject = new class ('c3c5cc1c-7968-4169-8b7d-6a028168a884') extends TestCase implements MiddlewareInterface
        {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $this->createMock(ResponseInterface::class);
            }
        };

        $this->mockedClassResolver->expects(self::once())->method('resolveClass')->with($expectedObject::class)->willReturn($expectedObject);
        $this->assertSame($expectedObject, $this->createResolver()->resolveMiddleware($expectedObject::class));
    }

    public function testResolveClassMethodName(): void
    {
        $testObject = $this->createTestObject();
        $actualServerRequestArg = self::callback(static fn(ReflectionParameter $p) => $p->name === 'actualServerRequest');
        $actualServerRequestBodyArg = self::callback(static fn(ReflectionParameter $p) => $p->name === 'actualServerRequestBody');
        $actualServerRequestHandlerArg = self::callback(static fn(ReflectionParameter $p) => $p->name === 'actualServerRequestHandler');
        $parametersResolver = fn(): Generator => yield from [$this->mockedServerRequest, $this->mockedServerRequestBody, $this->mockedServerRequestHandler];

        $this->mockedClassResolver->expects(self::once())->method('resolveClass')->with($testObject::class)->willReturn($testObject);
        $this->mockedParameterResolverChain->expects(self::once())->method('withContext')->with($this->mockedServerRequest)->willReturnSelf();
        $this->mockedParameterResolverChain->expects(self::once())->method('withResolver')->willReturnSelf();
        $this->mockedParameterResolverChain->expects(self::once())->method('resolveParameters')->with($actualServerRequestArg, $actualServerRequestBodyArg, $actualServerRequestHandlerArg)->willReturnCallback($parametersResolver);
        $this->mockedResponseResolverChain->expects(self::once())->method('resolveResponse')->with($this->mockedResponse)->willReturn($this->mockedResponse);
        $this->assertSame($this->mockedResponse, $this->createResolver()->resolveMiddleware([$testObject::class, 'test'])->process($this->mockedServerRequest, $this->mockedServerRequestHandler));
    }

    public function testResolveObjectMethodName(): void
    {
        $testObject = $this->createTestObject();
        $actualServerRequestArg = self::callback(static fn(ReflectionParameter $p) => $p->name === 'actualServerRequest');
        $actualServerRequestBodyArg = self::callback(static fn(ReflectionParameter $p) => $p->name === 'actualServerRequestBody');
        $actualServerRequestHandlerArg = self::callback(static fn(ReflectionParameter $p) => $p->name === 'actualServerRequestHandler');
        $parametersResolver = fn(): Generator => yield from [$this->mockedServerRequest, $this->mockedServerRequestBody, $this->mockedServerRequestHandler];

        $this->mockedClassResolver->expects(self::never())->method('resolveClass');
        $this->mockedParameterResolverChain->expects(self::once())->method('withContext')->with($this->mockedServerRequest)->willReturnSelf();
        $this->mockedParameterResolverChain->expects(self::once())->method('withResolver')->willReturnSelf();
        $this->mockedParameterResolverChain->expects(self::once())->method('resolveParameters')->with($actualServerRequestArg, $actualServerRequestBodyArg, $actualServerRequestHandlerArg)->willReturnCallback($parametersResolver);
        $this->mockedResponseResolverChain->expects(self::once())->method('resolveResponse')->with($this->mockedResponse)->willReturn($this->mockedResponse);
        $this->assertSame($this->mockedResponse, $this->createResolver()->resolveMiddleware([$testObject, 'test'])->process($this->mockedServerRequest, $this->mockedServerRequestHandler));
    }

    public function testResolveUnsupportedReference(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The middleware reference "foo" could not be resolved.');
        $this->createResolver()->resolveMiddleware('foo');
    }

    private function createTestObject(): object
    {
        return new class (
            name: '85160283-1280-4020-90bf-9cdca4891fbf',
            expectedServerRequest: $this->mockedServerRequest,
            expectedServerRequestBody: $this->mockedServerRequestBody,
            expectedServerRequestHandler: $this->mockedServerRequestHandler,
            response: $this->mockedResponse,
        ) extends TestCase {
            public function __construct(
                string $name,
                private readonly ServerRequestInterface $expectedServerRequest,
                private readonly StreamInterface $expectedServerRequestBody,
                private readonly RequestHandlerInterface $expectedServerRequestHandler,
                private readonly ResponseInterface $response,
            ) {
                parent::__construct($name);
            }

            public function test(
                ServerRequestInterface $actualServerRequest,
                StreamInterface $actualServerRequestBody,
                RequestHandlerInterface $actualServerRequestHandler,
            ): ResponseInterface {
                $this->assertSame($this->expectedServerRequest, $actualServerRequest);
                $this->assertSame($this->expectedServerRequestBody, $actualServerRequestBody);
                $this->assertSame($this->expectedServerRequestHandler, $actualServerRequestHandler);
                return $this->response;
            }
        };
    }
}
