<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Event\RoutePostRunEvent;
use Sunrise\Http\Router\Event\RoutePreRunEvent;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Router;

final class RouterTest extends TestCase
{
    use TestKit;

    private EventDispatcherInterface&MockObject $mockedEventDispatcher;
    private ReferenceResolverInterface&MockObject $mockedReferenceResolver;
    private RequestHandlerInterface&MockObject $mockedRequestHandler;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedEventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->mockedReferenceResolver = $this->createMock(ReferenceResolverInterface::class);
        $this->mockedRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    public function testLoadRoutes(): void
    {
        $fooRoute = $this->mockRoute('foo');
        $barRoute = $this->mockRoute('bar');
        $bazRoute = $this->mockRoute('baz');

        $this->assertSame([
            'foo' => $fooRoute,
            'bar' => $barRoute,
            'baz' => $bazRoute,
        ], (new Router(referenceResolver: $this->mockedReferenceResolver, loaders: [
            $this->mockLoader([], calls: 1),
            $this->mockLoader([$fooRoute], calls: 1),
            $this->mockLoader([$barRoute, $bazRoute], calls: 1),
        ]))->getRoutes());
    }

    public function testLoadDuplicateRoute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The route "foo" already exists.');

        (new Router(referenceResolver: $this->mockedReferenceResolver, loaders: [
            $this->mockLoader([$this->mockRoute('foo')], calls: 1),
            $this->mockLoader([$this->mockRoute('foo')], calls: 1),
        ]))->getRoutes();
    }

    public function testGetRoute(): void
    {
        $route = $this->mockRoute('foo');

        $this->assertSame($route, (new Router(referenceResolver: $this->mockedReferenceResolver, loaders: [
            $this->mockLoader([$route], calls: 1),
        ]))->getRoute('foo'));
    }

    public function testGetUnknownRoute(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The route "foo" does not exist.');

        (new Router(referenceResolver: $this->mockedReferenceResolver, loaders: []))->getRoute('foo');
    }

    public function testHasRouteFalse(): void
    {
        $this->assertFalse((new Router(referenceResolver: $this->mockedReferenceResolver, loaders: []))->hasRoute('foo'));
    }

    public function testHasRouteTrue(): void
    {
        $this->assertTrue((new Router(referenceResolver: $this->mockedReferenceResolver, loaders: [
            $this->mockLoader([$this->mockRoute('foo')], calls: 1),
        ]))->hasRoute('foo'));
    }

    public function testHandle(): void
    {
        $route = $this->mockRoute('test', path: '/test/{foo<(?:bar)>}/{bar<(?:baz)>}', requestHandler: '@test');
        $route->expects(self::once())->method('getAttributes')->willReturn(['foo' => 'bar', 'bar' => 'baz']);
        $route->expects(self::once())->method('withAddedAttributes')->with(['foo' => 'bar', 'bar' => 'baz'])->willReturnSelf();

        $request = $this->mockServerRequest(path: '/test/bar/%62%61%7a');

        $request->expects(self::exactly(3))->method('withAttribute')->willReturnCallback(
            static function ($name, $value) use ($route, $request) {
                self::assertContains([$name, $value], [['foo', 'bar'], ['bar', 'baz'], [RouteInterface::class, $route]]);
                return $request;
            }
        );

        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($request)->willReturn($this->mockedResponse);

        $this->mockedReferenceResolver->expects(self::atLeastOnce())->method('resolveMiddleware')->withAnyParameters()->willReturnArgument(0);
        $this->mockedReferenceResolver->expects(self::once())->method('resolveRequestHandler')->with('@test')->willReturn($this->mockedRequestHandler);

        $eventOverriddenRequest = $this->createMock(ServerRequestInterface::class);
        $eventOverriddenResponse = $this->createMock(ResponseInterface::class);

        $this->mockedEventDispatcher->expects(self::exactly(2))->method('dispatch')->withAnyParameters()->willReturnCallback(
            static function (object $event) use ($eventOverriddenRequest, $eventOverriddenResponse) {
                self::assertContains($event::class, [RoutePreRunEvent::class, RoutePostRunEvent::class]);

                if ($event instanceof RoutePreRunEvent) {
                    $event->request = $eventOverriddenRequest;
                }
                if ($event instanceof RoutePostRunEvent) {
                    $event->response = $eventOverriddenResponse;
                }
            }
        );

        $router = new Router(
            referenceResolver: $this->mockedReferenceResolver,
            loaders: [
                $this->mockLoader([
                    $this->mockRoute('bar', path: '/bar'),
                    $this->mockRoute('baz', path: '/baz'),
                ], calls: 1),
                $this->mockLoader([$route], calls: 1),
            ],
            middlewares: [
                $this->mockChainContinuingMiddleware(request: $request, calls: 1),
                $this->mockChainContinuingMiddleware(request: $request, calls: 1),
            ],
            routeMiddlewares: [
                $this->mockChainContinuingMiddleware(request: $eventOverriddenRequest, calls: 1),
                $this->mockChainContinuingMiddleware(request: $eventOverriddenRequest, calls: 1),
            ],
            eventDispatcher: $this->mockedEventDispatcher,
        );

        $this->assertSame($eventOverriddenResponse, $router->handle($request));
    }
}
