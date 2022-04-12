<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Event\RouteEvent;
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\PageNotFoundException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\ServerRequest\ServerRequestFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Import functions
 */
use function array_merge;
use function array_unique;

/**
 * RouterTest
 */
class RouterTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $router = new Router();

        $this->assertInstanceOf(MiddlewareInterface::class, $router);
        $this->assertInstanceOf(RequestHandlerInterface::class, $router);
    }

    /**
     * @return void
     */
    public function testAddPatterns() : void
    {
        $backup = Router::$patterns;

        $router = new Router();

        try {
            Router::$patterns = [];

            $router->addPatterns([
                '@foo' => 'foo',
                '@bar' => 'bar',
            ]);

            $this->assertSame([
                '@foo' => 'foo',
                '@bar' => 'bar',
            ], Router::$patterns);

            $router->addPatterns([
                '@baz' => 'baz',
            ]);

            $this->assertSame([
                '@foo' => 'foo',
                '@bar' => 'bar',
                '@baz' => 'baz',
            ], Router::$patterns);

            $router->addPatterns([
                '@bar' => 'qux',
            ]);

            $this->assertSame([
                '@foo' => 'foo',
                '@bar' => 'qux',
                '@baz' => 'baz',
            ], Router::$patterns);
        } finally {
            Router::$patterns = $backup;
        }
    }

    /**
     * @return void
     */
    public function testAddHosts() : void
    {
        $router = new Router();

        $this->assertSame([], $router->getHosts());

        $router->addHosts([
            'foo' => ['foo.com', 'www.foo.com'],
            'bar' => ['bar.com', 'www.bar.com'],
        ]);

        $this->assertSame([
            'foo' => ['foo.com', 'www.foo.com'],
            'bar' => ['bar.com', 'www.bar.com'],
        ], $router->getHosts());

        $router->addHosts([
            'baz' => ['baz.com', 'www.baz.com'],
        ]);

        $this->assertSame([
            'foo' => ['foo.com', 'www.foo.com'],
            'bar' => ['bar.com', 'www.bar.com'],
            'baz' => ['baz.com', 'www.baz.com'],
        ], $router->getHosts());

        $router->addHosts([
            'bar' => ['qux.com', 'www.qux.com'],
        ]);

        $this->assertSame([
            'foo' => ['foo.com', 'www.foo.com'],
            'bar' => ['qux.com', 'www.qux.com'],
            'baz' => ['baz.com', 'www.baz.com'],
        ], $router->getHosts());
    }

    /**
     * @return void
     */
    public function testAddHost() : void
    {
        $router = new Router();

        $this->assertSame([], $router->getHosts());

        $router->addHost('google', 'google.com', 'www.google.com');

        $this->assertSame([
            'google' => ['google.com', 'www.google.com'],
        ], $router->getHosts());

        $router->addHost('yahoo', 'yahoo.com');

        $this->assertSame([
            'google' => ['google.com', 'www.google.com'],
            'yahoo' => ['yahoo.com'],
        ], $router->getHosts());

        $router->addHost('google', 'localhost');

        $this->assertSame([
            'google' => ['localhost'],
            'yahoo' => ['yahoo.com'],
        ], $router->getHosts());
    }

    /**
     * @return void
     */
    public function testAddRoute() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $this->assertSame($routes, $router->getRoutes());
    }

    /**
     * @return void
     */
    public function testAddExistingRoute() : void
    {
        $route = new Fixtures\Route();

        $router = new Router();
        $router->addRoute($route);

        $this->expectException(InvalidArgumentException::class);

        try {
            $router->addRoute($route);
        } catch (InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testAddMiddleware() : void
    {
        $middlewares = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $router = new Router();
        $router->addMiddleware(...$middlewares);

        $this->assertSame($middlewares, $router->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testAddExistingMiddleware() : void
    {
        $middleware = new Fixtures\Middlewares\BlankMiddleware();

        $router = new Router();
        $router->addMiddleware($middleware);

        $this->expectException(InvalidArgumentException::class);

        try {
            $router->addMiddleware($middleware);
        } catch (InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testGetAllowedMethods() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $expected = array_unique(array_merge(
            $routes[0]->getMethods(),
            $routes[1]->getMethods(),
            $routes[2]->getMethods()
        ));

        $router = new Router();

        $this->assertSame([], $router->getAllowedMethods());

        $router->addRoute(...$routes);

        $this->assertSame($expected, $router->getAllowedMethods());
    }

    /**
     * @return void
     */
    public function testGetRoute() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $this->assertSame($routes[1], $router->getRoute($routes[1]->getName()));
    }

    /**
     * @return void
     */
    public function testGetUndefinedRoute() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $this->expectException(RouteNotFoundException::class);

        try {
            $router->getRoute('foo');
        } catch (RouteNotFoundException $e) {
            throw $e;
        }
    }

    /**
     * The test method only proxies the function path_build,
     * the function should be tested separately.
     *
     * @return void
     */
    public function testGenerateUri() : void
    {
        $route = new Fixtures\Route();

        $router = new Router();
        $router->addRoute($route);

        $this->assertSame($route->getPath(), $router->generateUri($route->getName()));
    }

    /**
     * @return void
     */
    public function testMatch() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $foundRoute = $router->match((new ServerRequestFactory)
            ->createServerRequest(
                $routes[2]->getMethods()[1],
                $routes[2]->getPath()
            ));

        $this->assertSame($routes[2]->getName(), $foundRoute->getName());
    }

    /**
     * @return void
     */
    public function testMatchWithUnallowedMethod() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $routes[1]->setPath('/foo');
        $routes[2]->setPath('/foo');
        $routes[3]->setPath('/foo');

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest('GET', '/foo');

        $this->expectException(MethodNotAllowedException::class);

        try {
            $router->match($request);
        } catch (MethodNotAllowedException $e) {
            $expected = array_unique(array_merge(
                $routes[1]->getMethods(),
                $routes[2]->getMethods(),
                $routes[3]->getMethods()
            ));

            $this->assertSame($request->getMethod(), $e->fromContext('method'));
            $this->assertSame($request->getMethod(), $e->getMethod());

            $this->assertSame($expected, $e->fromContext('allowed'));
            $this->assertSame($expected, $e->getAllowedMethods());

            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testMatchWithUndefinedRoute() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest($routes[0]->getMethods()[0], '/');

        $this->expectException(PageNotFoundException::class);

        try {
            $router->match($request);
        } catch (PageNotFoundException $e) {
            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testRun() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $router->run((new ServerRequestFactory)
            ->createServerRequest(
                $routes[1]->getMethods()[1],
                $routes[1]->getPath()
            ));

        $this->assertNotNull($router->getMatchedRoute());
        $this->assertSame($routes[1]->getName(), $router->getMatchedRoute()->getName());
        $this->assertTrue($routes[1]->getRequestHandler()->isRunned());
    }

    /**
     * @return void
     */
    public function testRunWithUnallowedMethod() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $router->addMiddleware(new CallableMiddleware(function ($request, $handler) {
            try {
                return $handler->handle($request);
            } catch (MethodNotAllowedException $e) {
                return (new ResponseFactory)->createResponse(405);
            }
        }));

        $response = $router->run((new ServerRequestFactory)
            ->createServerRequest('UNALLOWED', $routes[1]->getPath()));

        $this->assertSame(405, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testRunWithUndefinedRoute() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $router->addMiddleware(new CallableMiddleware(function ($request, $handler) {
            try {
                return $handler->handle($request);
            } catch (RouteNotFoundException $e) {
                return (new ResponseFactory)->createResponse(404);
            }
        }));

        $response = $router->run((new ServerRequestFactory)
            ->createServerRequest($routes[1]->getMethods()[1], '/undefined'));

        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testHandle() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $router->handle((new ServerRequestFactory)
            ->createServerRequest(
                $routes[2]->getMethods()[1],
                $routes[2]->getPath()
            ));

        $this->assertNotNull($router->getMatchedRoute());
        $this->assertSame($routes[2]->getName(), $router->getMatchedRoute()->getName());
        $this->assertTrue($routes[2]->getRequestHandler()->isRunned());
    }

    /**
     * @return void
     */
    public function testHandleWithMiddlewares() : void
    {
        $route = new Fixtures\Route();

        $middlewares = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $router = new Router();
        $router->addRoute($route);
        $router->addMiddleware(...$middlewares);

        $router->handle((new ServerRequestFactory)
            ->createServerRequest(
                $route->getMethods()[0],
                $route->getPath()
            ));

        $this->assertTrue($middlewares[0]->isRunned());
        $this->assertTrue($middlewares[1]->isRunned());
        $this->assertTrue($middlewares[2]->isRunned());
        $this->assertTrue($route->getRequestHandler()->isRunned());
    }

    /**
     * @return void
     */
    public function testHandleWithBrokenMiddleware() : void
    {
        $route = new Fixtures\Route();

        $middlewares = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(true),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $router = new Router();
        $router->addRoute($route);
        $router->addMiddleware(...$middlewares);

        $router->handle((new ServerRequestFactory)
            ->createServerRequest(
                $route->getMethods()[0],
                $route->getPath()
            ));

        $this->assertTrue($middlewares[0]->isRunned());
        $this->assertTrue($middlewares[1]->isRunned());
        $this->assertFalse($middlewares[2]->isRunned());
        $this->assertFalse($route->getRequestHandler()->isRunned());
    }

    /**
     * @return void
     */
    public function testHandleWithUnallowedMethod() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest('GET', $routes[1]->getPath());

        $this->expectException(MethodNotAllowedException::class);

        try {
            $router->handle($request);
        } catch (MethodNotAllowedException $e) {
            $allowedMethods = $routes[1]->getMethods();

            // $this->assertSame('GET', $e->fromContext('method'));
            $this->assertSame($allowedMethods, $e->fromContext('allowed'));
            $this->assertSame($allowedMethods, $e->getAllowedMethods());

            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testHandleWithUndefinedRoute() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest($routes[0]->getMethods()[0], '/');

        $this->expectException(RouteNotFoundException::class);

        try {
            $router->handle($request);
        } catch (RouteNotFoundException $e) {
            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testProcess() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $fallback = new Fixtures\Controllers\BlankController();

        $router->process((new ServerRequestFactory)
            ->createServerRequest(
                $routes[2]->getMethods()[1],
                $routes[2]->getPath()
            ), $fallback);

        $this->assertTrue($routes[2]->getRequestHandler()->isRunned());
        $this->assertFalse($fallback->isRunned());
    }

    /**
     * @return void
     */
    public function testProcessWithUnallowedMethod() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest('GET', $routes[0]->getPath());

        $fallback = new Fixtures\Controllers\BlankController();

        $router->process($request, $fallback);

        $this->assertInstanceOf(
            MethodNotAllowedException::class,
            $fallback->getRequest()->getAttribute(Router::ATTR_NAME_FOR_ROUTING_ERROR)
        );

        $this->assertTrue($fallback->isRunned());
    }

    /**
     * @return void
     */
    public function testProcessWithUndefinedRoute() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest($routes[0]->getMethods()[0], '/');

        $fallback = new Fixtures\Controllers\BlankController();

        $router->process($request, $fallback);

        $this->assertInstanceOf(
            RouteNotFoundException::class,
            $fallback->getRequest()->getAttribute(Router::ATTR_NAME_FOR_ROUTING_ERROR)
        );

        $this->assertTrue($fallback->isRunned());
    }

    /**
     * @return void
     */
    public function testLoad() : void
    {
        $router = new Router();

        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('load')
            ->willReturn(new RouteCollection(...$routes));

        $router->load($loader);

        $this->assertSame($routes, $router->getRoutes());
    }

    /**
     * @return void
     */
    public function testMultipleLoad() : void
    {
        $router = new Router();

        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $loaders = [];

        $loaders[0] = $this->createMock(LoaderInterface::class);
        $loaders[0]->method('load')
            ->willReturn(new RouteCollection($routes[0]));

        $loaders[1] = $this->createMock(LoaderInterface::class);
        $loaders[1]->method('load')
            ->willReturn(new RouteCollection($routes[1]));

        $loaders[2] = $this->createMock(LoaderInterface::class);
        $loaders[2]->method('load')
            ->willReturn(new RouteCollection($routes[2]));

        $router->load(...$loaders);

        $this->assertSame($routes, $router->getRoutes());
    }

    /**
     * @return void
     */
    public function testMatchWithHosts() : void
    {
        $requestHandler = new Fixtures\Controllers\BlankController();

        $routes = [
            new Route('foo', '/ping', ['GET'], $requestHandler),
            new Route('bar', '/ping', ['GET'], $requestHandler),
            new Route('baz', '/ping', ['GET'], $requestHandler),
            new Route('qux', '/ping', ['GET'], $requestHandler),
        ];

        $routes[0]->setHost('foo');
        $routes[1]->setHost('bar');
        $routes[2]->setHost('baz');

        $router = new Router();
        $router->addHost('foo', 'foo.net');
        $router->addHost('bar', 'bar.net');
        $router->addHost('baz', 'baz.net');
        $router->addRoute(...$routes);

        // hosted route
        $foundRoute = $router->match((new ServerRequestFactory)
            ->createServerRequest('GET', 'http://foo.net/ping'));
        $this->assertSame($routes[0]->getName(), $foundRoute->getName());

        // hosted route
        $foundRoute = $router->match((new ServerRequestFactory)
            ->createServerRequest('GET', 'http://bar.net/ping'));
        $this->assertSame($routes[1]->getName(), $foundRoute->getName());

        // hosted route
        $foundRoute = $router->match((new ServerRequestFactory)
            ->createServerRequest('GET', 'http://baz.net/ping'));
        $this->assertSame($routes[2]->getName(), $foundRoute->getName());

        // non-hosted route
        $foundRoute = $router->match((new ServerRequestFactory)
            ->createServerRequest('GET', 'http://localhost/ping'));
        $this->assertSame($routes[3]->getName(), $foundRoute->getName());
    }

    /**
     * @return void
     */
    public function testEventDispatcher() : void
    {
        $router = new Router();
        $this->assertNull($router->getEventDispatcher());

        $eventDispatcher = new EventDispatcher();
        $router->setEventDispatcher($eventDispatcher);
        $this->assertSame($eventDispatcher, $router->getEventDispatcher());

        $router->setEventDispatcher(null);
        $this->assertNull($router->getEventDispatcher());
    }

    /**
     * @return void
     */
    public function testRouteEvent() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $request = (new ServerRequestFactory)
            ->createServerRequest(
                $routes[1]->getMethods()[1],
                $routes[1]->getPath()
            );

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(RouteEvent::NAME, function (RouteEvent $event) use ($routes, $request) {
            $this->assertSame($routes[1]->getName(), $event->getRoute()->getName());
            $this->assertSame($request, $event->getRequest());
        });

        $router = new Router();
        $router->addRoute(...$routes);
        $router->setEventDispatcher($eventDispatcher);
        $router->run($request);
    }

    /**
     * @return void
     */
    public function testRouteEventOverrideRequest() : void
    {
        $route = new Fixtures\Route();

        $request = (new ServerRequestFactory)
            ->createServerRequest(
                $route->getMethods()[0],
                $route->getPath()
            );

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(RouteEvent::NAME, function (RouteEvent $event) use ($request) {
            $event->setRequest($request->withAttribute('foo', 'bar'));
            $this->assertNotSame($request, $event->getRequest());
        });

        $router = new Router();
        $router->addRoute($route);
        $router->setEventDispatcher($eventDispatcher);
        $router->handle($request);
    }

    /**
     * @return void
     */
    public function testResolveHost() : void
    {
        $router = new Router();
        $router->addHost('foo', 'www1.foo.com', 'www2.foo.com');
        $router->addHost('bar', 'www1.bar.com', 'www2.bar.com');

        $this->assertSame('foo', $router->resolveHostname('www1.foo.com'));
        $this->assertSame('foo', $router->resolveHostname('www2.foo.com'));
        $this->assertSame('bar', $router->resolveHostname('www1.bar.com'));
        $this->assertSame('bar', $router->resolveHostname('www2.bar.com'));
        $this->assertNull($router->resolveHostname('example.com'));
    }

    /**
     * @return void
     */
    public function testGetRoutesByHostname() : void
    {
        $router = new Router();
        $router->addHost('foo', 'www1.foo.com', 'www2.foo.com');
        $router->addHost('bar', 'www1.bar.com', 'www2.bar.com');

        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $routes[0]->setHost('foo');
        $routes[2]->setHost('bar');
        $routes[4]->setHost('bar');

        $router->addRoute(...$routes);

        $this->assertSame([
            $routes[0],
            $routes[1],
            $routes[3],
            $routes[5],
        ], $router->getRoutesByHostname('www1.foo.com'));

        $this->assertSame([
            $routes[0],
            $routes[1],
            $routes[3],
            $routes[5],
        ], $router->getRoutesByHostname('www2.foo.com'));

        $this->assertSame([
            $routes[1],
            $routes[2],
            $routes[3],
            $routes[4],
            $routes[5],
        ], $router->getRoutesByHostname('www1.bar.com'));

        $this->assertSame([
            $routes[1],
            $routes[2],
            $routes[3],
            $routes[4],
            $routes[5],
        ], $router->getRoutesByHostname('www2.bar.com'));

        $this->assertSame([
            $routes[1],
            $routes[3],
            $routes[5],
        ], $router->getRoutesByHostname('localhost'));
    }
}
