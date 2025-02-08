<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Router;

final class RouterTest extends TestCase
{
    use TestKit;

    private ServerRequestInterface&MockObject $mockedRequest;
    private RequestHandlerInterface&MockObject $mockedRequestHandler;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    public function testLoadRoutes(): void
    {
        $fooRoute = $this->mockRoute(name: 'foo');
        $barRoute = $this->mockRoute(name: 'bar');
        $bazRoute = $this->mockRoute(name: 'baz');

        $this->assertSame([
            'foo' => $fooRoute,
            'bar' => $barRoute,
            'baz' => $bazRoute,
        ], (new Router(loaders: [
            $this->mockLoader(routes: []),
            $this->mockLoader(routes: [$fooRoute]),
            $this->mockLoader(routes: [$barRoute, $bazRoute]),
        ]))->getRoutes());
    }

    public function testLoadDuplicateRoute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The route "foo" already exists.');

        (new Router(loaders: [
            $this->mockLoader(routes: [$this->mockRoute(name: 'foo')]),
            $this->mockLoader(routes: [$this->mockRoute(name: 'foo')]),
        ]))->getRoutes();
    }

    public function testGetRoute(): void
    {
        $route = $this->mockRoute(name: 'foo');

        $this->assertSame($route, (new Router(loaders: [
            $this->mockLoader(routes: [$route]),
        ]))->getRoute('foo'));
    }

    public function testGetUnknownRoute(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The route "foo" does not exist.');

        (new Router())->getRoute('foo');
    }

    public function testHasRouteFalse(): void
    {
        $this->assertFalse((new Router())->hasRoute('foo'));
    }

    public function testHasRouteTrue(): void
    {
        $this->assertTrue((new Router(loaders: [
            $this->mockLoader(routes: [$this->mockRoute(name: 'foo')]),
        ]))->hasRoute('foo'));
    }

    public function testUsingMiddlewareWhenHandlingRequest(): void
    {
        $this->assertSame($this->mockedResponse, (new Router(
            middlewares: [
                $this->mockChainContinuingMiddleware($this->mockedRequest),
                $this->mockChainBreakingMiddleware($this->mockedResponse, $this->mockedRequest),
            ],
        ))->handle($this->mockedRequest));
    }
}
