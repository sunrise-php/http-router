<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use Generator;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
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
use Sunrise\Http\Router\Tests\Fixture\Tests\IntBackedEnum;
use Sunrise\Http\Router\Tests\Fixture\Tests\StringableClass;

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

    public function testMatch(): void
    {
        $route = $this->mockRoute('test', path: '/test', nameCalls: 2, pathCalls: 1, methodsCalls: 1);
        $request = $this->mockServerRequest(path: '/test', methodCalls: 1, pathCalls: 1);
        self::assertSame($route, $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request));
    }

    public function testMatchWithAttributes(): void
    {
        $route = $this->mockRoute('test', path: '/test/{id}', nameCalls: 2, pathCalls: 1, methodsCalls: 1);
        $route->expects(self::once())->method('withAddedAttributes')->with(['id' => '1'])->willReturnSelf();
        $request = $this->mockServerRequest(path: '/test/1', methodCalls: 1, pathCalls: 1);
        self::assertSame($route, $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request));
    }

    public function testMatchWithoutAttributes(): void
    {
        $route = $this->mockRoute('test', path: '/test', nameCalls: 2, pathCalls: 1, methodsCalls: 1);
        $route->expects(self::never())->method('withAddedAttributes');
        $request = $this->mockServerRequest(path: '/test', methodCalls: 1, pathCalls: 1);
        self::assertSame($route, $this->createRouter([$this->mockLoader([$route], calls: 1)])->match($request));
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

    public function testRunRoute(): void
    {
        $route = $this->mockRoute('test', path: '/test', requestHandler: '@test');
        $request = $this->mockServerRequest(path: '/test');
        $request->expects(self::any())->method('withAttribute')->withAnyParameters()->willReturnSelf();
        $this->mockedReferenceResolver->expects(self::once())->method('resolveRequestHandler')->with('@test')->willReturn($this->mockedRequestHandler);
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($request)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, $this->createRouter([])->runRoute($route, $request));
    }

    public function testRunRouteAndPassAttributesToRequest(): void
    {
        $route = $this->mockRoute('test', path: '/test', requestHandler: $this->mockedRequestHandler);
        $route->expects(self::once())->method('getAttributes')->willReturn(['foo' => 'bar']);
        $request = $this->mockServerRequest(path: '/test');
        $request->expects(self::exactly(2))->method('withAttribute')->withAnyParameters()->willReturnCallback(
            static function ($name, $value) use ($route, $request) {
                self::assertContains([$name, $value], [[RouteInterface::class, $route], ['foo', 'bar']]);
                return $request;
            }
        );
        $this->mockedReferenceResolver->expects(self::once())->method('resolveRequestHandler')->withAnyParameters()->willReturnArgument(0);
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($request)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, $this->createRouter([])->runRoute($route, $request));
    }

    public function testRunRouteAndRoutePreRunEvent(): void
    {
        $route = $this->mockRoute('test', path: '/test', requestHandler: $this->mockedRequestHandler);
        $request = $this->mockServerRequest(path: '/test');
        $request->expects(self::any())->method('withAttribute')->withAnyParameters()->willReturnSelf();
        $overriddenRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedEventDispatcher->expects(self::any())->method('dispatch')->withAnyParameters()->willReturnCallback(
            static function (object $event) use ($request, $overriddenRequest) {
                if ($event instanceof RoutePreRunEvent) {
                    self::assertSame($request, $event->request);
                    $event->request = $overriddenRequest;
                }
            }
        );
        $this->mockedReferenceResolver->expects(self::once())->method('resolveRequestHandler')->withAnyParameters()->willReturnArgument(0);
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($overriddenRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, $this->createRouter([])->runRoute($route, $request));
    }

    public function testRunRouteAndRoutePostRunEvent(): void
    {
        $route = $this->mockRoute('test', path: '/test', requestHandler: $this->mockedRequestHandler);
        $request = $this->mockServerRequest(path: '/test');
        $request->expects(self::any())->method('withAttribute')->withAnyParameters()->willReturnSelf();
        $this->mockedReferenceResolver->expects(self::once())->method('resolveRequestHandler')->withAnyParameters()->willReturnArgument(0);
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($request)->willReturn($this->mockedResponse);
        $overriddenResponse = $this->createMock(ResponseInterface::class);
        $this->mockedEventDispatcher->expects(self::any())->method('dispatch')->withAnyParameters()->willReturnCallback(
            function (object $event) use ($overriddenResponse) {
                if ($event instanceof RoutePostRunEvent) {
                    self::assertSame($this->mockedResponse, $event->response);
                    $event->response = $overriddenResponse;
                }
            }
        );
        self::assertSame($overriddenResponse, $this->createRouter([])->runRoute($route, $request));
    }

    public function testRunRouteWithMiddlewares(): void
    {
        $request = $this->mockServerRequest(path: '/test');
        $routerMiddlewares = [$this->mockChainContinuingMiddleware(calls: 0)];
        $commonMiddlewares = [...$this->mockChainContinuingMiddlewares(count: 2, request: $request, calls: 1)];
        $routeMiddlewares = [...$this->mockChainContinuingMiddlewares(count: 2, request: $request, calls: 1)];
        $route = $this->mockRoute('test', path: '/test', requestHandler: $this->mockedRequestHandler, middlewares: $routeMiddlewares);
        $request->expects(self::any())->method('withAttribute')->withAnyParameters()->willReturnSelf();
        $this->mockedReferenceResolver->expects(self::once())->method('resolveRequestHandler')->withAnyParameters()->willReturnArgument(0);
        $this->mockedReferenceResolver->expects(self::exactly(4))->method('resolveMiddleware')->withAnyParameters()->willReturnArgument(0);
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($request)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, $this->createRouter([], middlewares: $routerMiddlewares, routeMiddlewares: $commonMiddlewares)->runRoute($route, $request));
    }

    #[DataProvider('buildRouteDataProvider')]
    public function testBuildRoute(string $routePath, string $expectedPath, array $variables = [], bool $strictly = false): void
    {
        $route = $this->mockRoute('test', path: $routePath);
        self::assertSame($expectedPath, $this->createRouter([])->buildRoute($route, $variables, $strictly));
    }

    public static function buildRouteDataProvider(): Generator
    {
        yield ['/test', '/test'];
        yield ['/test/{a}', '/test/1', ['a' => '1']];
        yield ['/test/{a}/{b}', '/test/1/2', ['a' => '1', 'b' => '2']];
        yield ['/test/{a<\d+>}', '/test/1', ['a' => '1'], true];
        yield ['/test/{a<\d+>}', '/test/foo', ['a' => 'foo'], false];
        yield ['/test(/{a})', '/test'];
        yield ['/test(/{a})', '/test/1', ['a' => '1']];
        yield ['/test/{a}', '/test/1', ['a' => 1]];
        yield ['/test/{a}', '/test/1', ['a' => IntBackedEnum::One]];
        yield ['/test/{a}', '/test/1', ['a' => new StringableClass('1')]];
    }

    #[DataProvider('failedBuildRouteDataProvider')]
    public function testFailedBuildRoute(string $routePath, string $expectedMessageRegex, array $variables = [], bool $strictly = false): void
    {
        $route = $this->mockRoute('test', path: $routePath);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches($expectedMessageRegex);
        $this->createRouter([])->buildRoute($route, $variables, $strictly);
    }

    public static function failedBuildRouteDataProvider(): Generator
    {
        yield ['/test/{a}', '/the required value for the variable {a} is missing/'];
        yield ['/test/{a}', '/the required value for the variable {a} is missing/', ['a' => null]];
        yield ['/test/{a}', '/supported types are: string, integer, backed enum and stringable object/', ['a' => .0]];
        yield ['/test/{a}', '/supported types are: string, integer, backed enum and stringable object/', ['a' => []]];
        yield ['/test/{a}', '/supported types are: string, integer, backed enum and stringable object/', ['a' => (object) []]];
        yield ['/test/{a<\d+>}', '/one of the values does not match its pattern/', ['a' => 'foo'], true];
    }

    public function testCachingRouteRequestHandler(): void
    {
        $route = $this->mockRoute('test');
        $router = $this->createRouter([]);
        self::assertSame($router->getRouteRequestHandler($route), $router->getRouteRequestHandler($route));
    }

    public function testCachingRouterRequestHandler(): void
    {
        $router = $this->createRouter([]);
        self::assertSame($router->getRequestHandler(), $router->getRequestHandler());
    }

    public function testHandle(): void
    {
        $request = $this->mockServerRequest(path: '/test/bar/%62%61%7a', methodCalls: 1, pathCalls: 1);

        $routerMiddlewares = [...$this->mockChainContinuingMiddlewares(count: 2, request: $request, calls: 1)];
        $routerRouteMiddlewares = [...$this->mockChainContinuingMiddlewares(count: 2, request: $request, calls: 1)];
        $routeMiddlewares = [...$this->mockChainContinuingMiddlewares(count: 2, request: $request, calls: 1)];

        // nameCalls: load; create regex; create request handler.
        $route = $this->mockRoute('test', path: '/test/{foo<(?:bar)>}/{bar<(?:baz)>}', requestHandler: '@test', middlewares: $routeMiddlewares, nameCalls: 3, pathCalls: 1, methodsCalls: 1, requestHandlerCalls: 1, middlewaresCalls: 1);
        $route->expects(self::once())->method('getAttributes')->willReturn(['foo' => 'bar', 'bar' => 'baz']);
        $route->expects(self::once())->method('withAddedAttributes')->with(['foo' => 'bar', 'bar' => 'baz'])->willReturnSelf();

        $request->expects(self::exactly(3))->method('withAttribute')->willReturnCallback(
            static function ($name, $value) use ($route, $request) {
                self::assertContains([$name, $value], [['foo', 'bar'], ['bar', 'baz'], [RouteInterface::class, $route]]);
                return $request;
            }
        );

        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($request)->willReturn($this->mockedResponse);
        $this->mockedReferenceResolver->expects(self::exactly(6))->method('resolveMiddleware')->withAnyParameters()->willReturnArgument(0);
        $this->mockedReferenceResolver->expects(self::once())->method('resolveRequestHandler')->with('@test')->willReturn($this->mockedRequestHandler);

        $eventOverriddenRequest = $this->createMock(ServerRequestInterface::class);
        $eventOverriddenResponse = $this->createMock(ResponseInterface::class);

        $this->mockedEventDispatcher->expects(self::exactly(2))->method('dispatch')->withAnyParameters()->willReturnCallback(
            static function (RoutePreRunEvent|RoutePostRunEvent $event) use ($eventOverriddenRequest, $eventOverriddenResponse) {
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
            middlewares: $routerMiddlewares,
            routeMiddlewares: $routerRouteMiddlewares,
        );

        self::assertSame($eventOverriddenResponse, $router->handle($request));
    }
}
