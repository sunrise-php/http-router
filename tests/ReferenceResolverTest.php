<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\MiddlewareResolverInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\RequestHandlerResolverInterface;

final class ReferenceResolverTest extends TestCase
{
    private MiddlewareResolverInterface&MockObject $middlewareResolver;
    private RequestHandlerResolverInterface&MockObject $requestHandlerResolver;

    protected function setUp(): void
    {
        $this->middlewareResolver = $this->createMock(MiddlewareResolverInterface::class);
        $this->requestHandlerResolver = $this->createMock(RequestHandlerResolverInterface::class);
    }

    public function testResolveMiddleware(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->middlewareResolver->expects(self::once())->method('resolveMiddleware')->with('foo')->willReturn($middleware);
        $this->assertSame($middleware, (new ReferenceResolver($this->middlewareResolver, $this->requestHandlerResolver))->resolveMiddleware('foo'));
    }

    public function testResolveRequestHandler(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->requestHandlerResolver->expects(self::once())->method('resolveRequestHandler')->with('foo')->willReturn($requestHandler);
        $this->assertSame($requestHandler, (new ReferenceResolver($this->middlewareResolver, $this->requestHandlerResolver))->resolveRequestHandler('foo'));
    }
}
