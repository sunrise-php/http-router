<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\MiddlewareResolverInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\RequestHandlerResolverInterface;
use Sunrise\Http\Router\ResponseResolverInterface;

final class ReferenceResolverTest extends TestCase
{
    use TestKit;

    private MiddlewareResolverInterface&MockObject $mockedMiddlewareResolver;
    private RequestHandlerResolverInterface&MockObject $mockedRequestHandlerResolver;
    private ServerRequestInterface&MockObject $mockedRequest;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedMiddlewareResolver = $this->createMock(MiddlewareResolverInterface::class);
        $this->mockedRequestHandlerResolver = $this->createMock(RequestHandlerResolverInterface::class);
        $this->mockedRequest = $this->mockServerRequest(ServerRequestInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    public function testResolveMiddleware(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->mockedMiddlewareResolver->expects(self::once())->method('resolveMiddleware')->with('foo')->willReturn($middleware);
        self::assertSame($middleware, (new ReferenceResolver($this->mockedMiddlewareResolver, $this->mockedRequestHandlerResolver))->resolveMiddleware('foo'));
    }

    public function testResolveRequestHandler(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedRequestHandlerResolver->expects(self::once())->method('resolveRequestHandler')->with('foo')->willReturn($requestHandler);
        self::assertSame($requestHandler, (new ReferenceResolver($this->mockedMiddlewareResolver, $this->mockedRequestHandlerResolver))->resolveRequestHandler('foo'));
    }

    public function testBuild(): void
    {
        $testObject = new class
        {
            public function middleware(mixed $foo, mixed $bar, ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                TestCase::assertSame('bar', $foo);
                TestCase::assertSame('baz', $bar);
                return $handler->handle($request);
            }

            public function requestHandler(mixed $foo, mixed $bar): string
            {
                TestCase::assertSame('bar', $foo);
                TestCase::assertSame('baz', $bar);
                return '@response';
            }
        };

        $parameterResolvers = [];
        $this->mockParameterResolver('foo', value: 'bar', context: $this->mockedRequest, calls: self::atLeastOnce(), registry: $parameterResolvers);
        $this->mockParameterResolver('bar', value: 'baz', context: $this->mockedRequest, calls: self::atLeastOnce(), registry: $parameterResolvers);

        /** @var list<ResponseResolverInterface&MockObject> $responseResolvers */
        $responseResolvers = [
            $this->createMock(ResponseResolverInterface::class),
            $this->createMock(ResponseResolverInterface::class),
        ];

        $responseResolvers[0]->expects(self::atLeastOnce())->method('resolveResponse')->with('@response', self::anything(), $this->mockedRequest)->willReturn(null);
        $responseResolvers[1]->expects(self::atLeastOnce())->method('resolveResponse')->with('@response', self::anything(), $this->mockedRequest)->willReturn($this->mockedResponse);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::atLeastOnce())->method('has')->with($testObject::class)->willReturn(true);
        $container->expects(self::atLeastOnce())->method('get')->with($testObject::class)->willReturn($testObject);

        $referenceResolver = ReferenceResolver::build($parameterResolvers, $responseResolvers, $container);
        $middleware = $referenceResolver->resolveMiddleware([$testObject::class, 'middleware']);
        $requestHandler = $referenceResolver->resolveRequestHandler([$testObject::class, 'requestHandler']);

        self::assertSame($this->mockedResponse, $middleware->process($this->mockedRequest, $requestHandler));
    }
}
