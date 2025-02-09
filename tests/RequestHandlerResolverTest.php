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
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionParameter;
use Sunrise\Http\Router\ClassResolverInterface;
use Sunrise\Http\Router\ParameterResolverChainInterface;
use Sunrise\Http\Router\RequestHandlerResolver;
use Sunrise\Http\Router\ResponseResolverChainInterface;

final class RequestHandlerResolverTest extends TestCase
{
    private ClassResolverInterface&MockObject $mockedClassResolver;
    private ParameterResolverChainInterface&MockObject $mockedParameterResolverChain;
    private ResponseResolverChainInterface&MockObject $mockedResponseResolverChain;
    private ServerRequestInterface&MockObject $mockedRequest;
    private StreamInterface&MockObject $mockedRequestBody;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedClassResolver = $this->createMock(ClassResolverInterface::class);
        $this->mockedParameterResolverChain = $this->createMock(ParameterResolverChainInterface::class);
        $this->mockedResponseResolverChain = $this->createMock(ResponseResolverChainInterface::class);
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRequestBody = $this->createMock(StreamInterface::class);
        $this->mockedRequest->expects(self::any())->method('getBody')->willReturn($this->mockedRequestBody);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    private function createResolver(): RequestHandlerResolver
    {
        return new RequestHandlerResolver(
            classResolver: $this->mockedClassResolver,
            parameterResolverChain: $this->mockedParameterResolverChain,
            responseResolverChain: $this->mockedResponseResolverChain,
        );
    }

    public function testResolveObject(): void
    {
        $expectedObject = $this->createMock(RequestHandlerInterface::class);
        self::assertSame($expectedObject, $this->createResolver()->resolveRequestHandler($expectedObject));
    }

    public function testResolveClassName(): void
    {
        $expectedObject = new class ('c3c5cc1c-7968-4169-8b7d-6a028168a884') extends TestCase implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->createMock(ResponseInterface::class);
            }
        };

        $this->mockedClassResolver->expects(self::once())->method('resolveClass')->with($expectedObject::class)->willReturn($expectedObject);
        self::assertSame($expectedObject, $this->createResolver()->resolveRequestHandler($expectedObject::class));
    }

    public function testResolveClassMethodName(): void
    {
        $testObject = $this->createTestObject();
        $actualServerRequestArg = self::callback(static fn(ReflectionParameter $p) => $p->name === 'actualServerRequest');
        $actualServerRequestBodyArg = self::callback(static fn(ReflectionParameter $p) => $p->name === 'actualServerRequestBody');
        $parametersResolver = fn(): Generator => yield from [$this->mockedRequest, $this->mockedRequestBody];

        $this->mockedClassResolver->expects(self::once())->method('resolveClass')->with($testObject::class)->willReturn($testObject);
        $this->mockedParameterResolverChain->expects(self::once())->method('withContext')->with($this->mockedRequest)->willReturnSelf();
        $this->mockedParameterResolverChain->expects(self::once())->method('withResolver')->willReturnSelf();
        $this->mockedParameterResolverChain->expects(self::once())->method('resolveParameters')->with($actualServerRequestArg, $actualServerRequestBodyArg)->willReturnCallback($parametersResolver);
        $this->mockedResponseResolverChain->expects(self::once())->method('resolveResponse')->with($this->mockedResponse)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, $this->createResolver()->resolveRequestHandler([$testObject::class, 'test'])->handle($this->mockedRequest));
    }

    public function testResolveObjectMethodName(): void
    {
        $testObject = $this->createTestObject();
        $actualServerRequestArg = self::callback(static fn(ReflectionParameter $p) => $p->name === 'actualServerRequest');
        $actualServerRequestBodyArg = self::callback(static fn(ReflectionParameter $p) => $p->name === 'actualServerRequestBody');
        $parametersResolver = fn(): Generator => yield from [$this->mockedRequest, $this->mockedRequestBody];

        $this->mockedClassResolver->expects(self::never())->method('resolveClass');
        $this->mockedParameterResolverChain->expects(self::once())->method('withContext')->with($this->mockedRequest)->willReturnSelf();
        $this->mockedParameterResolverChain->expects(self::once())->method('withResolver')->willReturnSelf();
        $this->mockedParameterResolverChain->expects(self::once())->method('resolveParameters')->with($actualServerRequestArg, $actualServerRequestBodyArg)->willReturnCallback($parametersResolver);
        $this->mockedResponseResolverChain->expects(self::once())->method('resolveResponse')->with($this->mockedResponse)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, $this->createResolver()->resolveRequestHandler([$testObject, 'test'])->handle($this->mockedRequest));
    }

    public function testResolveUnsupportedReference(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The request handler reference "stdClass::bar" could not be resolved.');
        $this->createResolver()->resolveRequestHandler([new \stdClass(), 'bar']);
    }

    private function createTestObject(): object
    {
        return new class (
            name: '48cdd1aa-e58b-40ab-bb69-530bcba2984d',
            expectedServerRequest: $this->mockedRequest,
            expectedServerRequestBody: $this->mockedRequestBody,
            response: $this->mockedResponse,
        ) extends TestCase {
            public function __construct(
                string $name,
                private readonly ServerRequestInterface $expectedServerRequest,
                private readonly StreamInterface $expectedServerRequestBody,
                private readonly ResponseInterface $response,
            ) {
                parent::__construct($name);
            }

            public function test(
                ServerRequestInterface $actualServerRequest,
                StreamInterface $actualServerRequestBody,
            ): ResponseInterface {
                self::assertSame($this->expectedServerRequest, $actualServerRequest);
                self::assertSame($this->expectedServerRequestBody, $actualServerRequestBody);
                return $this->response;
            }
        };
    }
}
