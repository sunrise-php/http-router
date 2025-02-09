<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Dictionary\ErrorMessage;
use Sunrise\Http\Router\Event\RoutePostRunEvent;
use Sunrise\Http\Router\Event\RoutePreRunEvent;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Router;

final class RouterTest extends TestCase
{
    use TestKit;

    private ReferenceResolverInterface&MockObject $mockedReferenceResolver;
    private EventDispatcherInterface&MockObject $mockedEventDispatcher;
    private ServerRequestInterface&MockObject $mockedRequest;
    private RequestHandlerInterface&MockObject $mockedRequestHandler;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedReferenceResolver = $this->createMock(ReferenceResolverInterface::class);
        $this->mockedEventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    private function createRouter(
        array $loaders,
        array $middlewares = [],
        array $routeMiddlewares = [],
    ): Router {
        return new Router(
            referenceResolver: $this->mockedReferenceResolver,
            loaders: $loaders,
            middlewares: $middlewares,
            routeMiddlewares: $routeMiddlewares,
            eventDispatcher: $this->mockedEventDispatcher,
        );
    }

    public function testLoad(): void
    {
        $fooRoute = $this->mockRoute('foo', nameCalls: 1);
        $barRoute = $this->mockRoute('bar', nameCalls: 1);
        $bazRoute = $this->mockRoute('baz', nameCalls: 1);

        self::assertSame([
            'foo' => $fooRoute,
            'bar' => $barRoute,
            'baz' => $bazRoute,
        ], $this->createRouter([
            $this->mockLoader([], calls: 1),
            $this->mockLoader([$fooRoute], calls: 1),
            $this->mockLoader([$barRoute, $bazRoute], calls: 1),
        ])->getRoutes());
    }

    public function testDuplicateRoute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The route "foo" already exists.');
        $this->createRouter([
            $this->mockLoader([$this->mockRoute('foo', nameCalls: 1)], calls: 1),
            $this->mockLoader([$this->mockRoute('foo', nameCalls: 1)], calls: 1),
        ])->getRoutes();
    }

    public function testGetRoute(): void
    {
        $route = $this->mockRoute('foo', nameCalls: 1);
        self::assertSame($route, $this->createRouter([$this->mockLoader([$route], calls: 1)])->getRoute('foo'));
    }

    public function testUnknownRoute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The route "foo" does not exist.');
        $this->createRouter([])->getRoute('foo');
    }

    public function testHasRouteFalse(): void
    {
        self::assertFalse($this->createRouter([])->hasRoute('foo'));
    }

    public function testHasRouteTrue(): void
    {
        self::assertTrue($this->createRouter([$this->mockLoader([$this->mockRoute('foo', nameCalls: 1)], calls: 1)])->hasRoute('foo'));
    }

    public function testMatchWithoutRoutes(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The router does not contain any routes.');
        $this->createRouter([])->match($this->mockedRequest);
    }

    public function testMatchWithEncodedRequestPath(): void
    {
        $route = $this->mockRoute('test', path: '/test', nameCalls: 2, pathCalls: 1, methodsCalls: 1);
        $request = $this->mockServerRequest(path: '/%74%65%73%74', methodCalls: 1, pathCalls: 1);
        self::assertSame($route, $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request));
    }

    public function testMatchWithMalformedRequestPath(): void
    {
        $route = $this->mockRoute('test', path: '/test', nameCalls: 2, pathCalls: 1, methodsCalls: 0);
        $request = $this->mockServerRequest(path: '/test%FF', methodCalls: 1, pathCalls: 1);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::MALFORMED_URI);
        try {
            $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request);
        } catch (HttpException $e) {
            self::assertSame(400, $e->getCode());
            throw $e;
        }
    }

    public function testMatchWithUnresolvedRequestPath(): void
    {
        $route = $this->mockRoute('test', path: '/test/{id<\d+>}', nameCalls: 2, pathCalls: 1, methodsCalls: 0);
        $request = $this->mockServerRequest(path: '/test/foo', methodCalls: 1, pathCalls: 1);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::RESOURCE_NOT_FOUND);
        try {
            $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request);
        } catch (HttpException $e) {
            self::assertSame(404, $e->getCode());
            throw $e;
        }
    }

    public function testMatchWithEmptyRoutePattern(): void
    {
        $route = $this->mockRoute('test', path: '/test', nameCalls: 2, pathCalls: 0, methodsCalls: 0);
        $route->expects(self::any())->method('getPattern')->willReturn('');
        $request = $this->mockServerRequest(path: '/test', methodCalls: 1, pathCalls: 1);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/This problem is most likely related to one of the route patterns/');
        $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request);
    }

    public function testMatchWithInvalidRoutePattern(): void
    {
        $route = $this->mockRoute('test', path: '/test', nameCalls: 2, pathCalls: 0, methodsCalls: 0);
        $route->expects(self::any())->method('getPattern')->willReturn('#');
        $request = $this->mockServerRequest(path: '/test', methodCalls: 1, pathCalls: 1);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/This problem is most likely related to one of the route patterns/');
        $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request);
    }

    public function testMatchWithInvalidRouteVariablePattern(): void
    {
        $route = $this->mockRoute('test', path: '/test/{id}', nameCalls: 2, pathCalls: 1, methodsCalls: 0);
        $route->expects(self::any())->method('getPatterns')->willReturn(['id' => '#']);
        $request = $this->mockServerRequest(path: '/test/1', methodCalls: 1, pathCalls: 1);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/This problem is most likely related to one of the route patterns/');
        $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request);
    }

    public function testMatchWithInvalidRoutePathVariablePattern(): void
    {
        $route = $this->mockRoute('test', path: '/test/{id<][>}', nameCalls: 2, pathCalls: 1, methodsCalls: 0);
        $request = $this->mockServerRequest(path: '/test/1', methodCalls: 1, pathCalls: 1);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/This problem is most likely related to one of the route patterns/');
        $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request);
    }

    public function testMatchWithUnsupportedRequestMethod(): void
    {
        $route = $this->mockRoute('test', path: '/test', nameCalls: 2, pathCalls: 1, methodsCalls: 1);
        $request = $this->mockServerRequest(method: 'HEAD', path: '/test', methodCalls: 1, pathCalls: 1);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::METHOD_NOT_ALLOWED);
        try {
            $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request);
        } catch (HttpException $e) {
            self::assertSame(405, $e->getCode());
            self::assertContains(['Allow', 'GET'], $e->getHeaderFields());
            throw $e;
        }
    }

    public function testHandle(): void
    {
        // nameCalls: load; create regex; create request handler.
        $route = $this->mockRoute('test', path: '/test/{foo<(?:bar)>}/{bar<(?:baz)>}', requestHandler: '@test', nameCalls: 3, pathCalls: 1, methodsCalls: 1, requestHandlerCalls: 1);
        $route->expects(self::once())->method('getAttributes')->willReturn(['foo' => 'bar', 'bar' => 'baz']);
        $route->expects(self::once())->method('withAddedAttributes')->with(['foo' => 'bar', 'bar' => 'baz'])->willReturnSelf();

        $request = $this->mockServerRequest(path: '/test/bar/%62%61%7a', methodCalls: 1, pathCalls: 1);
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

        $router = $this->createRouter(
            loaders: [
                $this->mockLoader([
                    // nameCalls: load; create regex.
                    $this->mockRoute('foo', path: '/foo', nameCalls: 2, pathCalls: 1, methodsCalls: 0),
                    $this->mockRoute('bar', path: '/bar', nameCalls: 2, pathCalls: 1, methodsCalls: 0),
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
        );

        self::assertSame($eventOverriddenResponse, $router->handle($request));
    }
}
