<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\MiddlewareAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\Router;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * Import functions
 */
use function array_merge;

/**
 * RouterTest
 */
class RouterTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $router = new Router();

        $this->assertInstanceOf(MiddlewareInterface::class, $router);
        $this->assertInstanceOf(RequestHandlerInterface::class, $router);
    }

    /**
     * @return void
     */
    public function testAddRoute() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $this->assertSame($routes, $router->getRoutes());
    }

    /**
     * @return void
     */
    public function testAddMiddleware() : void
    {
        $middlewares = [
            new Fixture\BlankMiddleware(),
            new Fixture\BlankMiddleware(),
            new Fixture\BlankMiddleware(),
        ];

        $router = new Router();
        $router->addMiddleware(...$middlewares);

        $this->assertSame($middlewares, $router->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testAddExistingRoute() : void
    {
        $route = new Fixture\TestRoute();

        $router = new Router();
        $router->addRoute($route);

        // the given exception message should be tested through exceptions factory...
        $this->expectException(RouteAlreadyExistsException::class);

        try {
            $router->addRoute($route);
        } catch (RouteAlreadyExistsException $e) {
            // $this->assertSame($route, $e->fromContext('route'));

            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testAddExistingMiddleware() : void
    {
        $middleware = new Fixture\BlankMiddleware();

        $router = new Router();
        $router->addMiddleware($middleware);

        // the given exception message should be tested through exceptions factory...
        $this->expectException(MiddlewareAlreadyExistsException::class);

        try {
            $router->addMiddleware($middleware);
        } catch (MiddlewareAlreadyExistsException $e) {
            // $this->assertSame($middleware, $e->fromContext('middleware'));

            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testGetAllowedMethods() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $expectedMethods = array_merge(
            $routes[0]->getMethods(),
            $routes[1]->getMethods(),
            $routes[2]->getMethods()
        );

        $router = new Router();

        $this->assertSame([], $router->getAllowedMethods());

        $router->addRoute(...$routes);

        $this->assertSame($expectedMethods, $router->getAllowedMethods());
    }

    /**
     * @return void
     */
    public function testGetRoute() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
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
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        // the given exception message should be tested through exceptions factory...
        $this->expectException(RouteNotFoundException::class);

        try {
            $router->getRoute('foo');
        } catch (RouteNotFoundException $e) {
            // $this->assertSame('foo', $e->fromContext('name'));

            throw $e;
        }
    }

    /**
     * The test method only proxies the function `path_build`,
     * the function should be tested separately.
     *
     * @return void
     */
    public function testGenerateUri() : void
    {
        $route = new Fixture\TestRoute();

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
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
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
    public function testMatchForUnallowedMethod() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest('GET', $routes[0]->getPath());

        // the given exception message should be tested through exceptions factory...
        $this->expectException(MethodNotAllowedException::class);

        try {
            $router->match($request);
        } catch (MethodNotAllowedException $e) {
            $allowedMethods = array_merge(
                $routes[0]->getMethods(),
                $routes[1]->getMethods(),
                $routes[2]->getMethods()
            );

            // $this->assertSame('GET', $e->fromContext('method'));
            $this->assertSame($allowedMethods, $e->fromContext('allowed'));
            $this->assertSame($allowedMethods, $e->getAllowedMethods());

            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testMatchForUndefinedRoute() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest($routes[0]->getMethods()[0], '/');

        // the given exception message should be tested through exceptions factory...
        $this->expectException(RouteNotFoundException::class);

        try {
            $router->match($request);
        } catch (RouteNotFoundException $e) {
            // $this->assertSame('/', $e->fromContext('uri'));

            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testHandle() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $router->handle((new ServerRequestFactory)
            ->createServerRequest(
                $routes[2]->getMethods()[1],
                $routes[2]->getPath()
            ));

        $this->assertTrue($routes[2]->getRequestHandler()->isRunned());
    }

    /**
     * @return void
     */
    public function testHandleWithMiddlewares() : void
    {
        $route = new Fixture\TestRoute();

        $middlewares = [
            new Fixture\BlankMiddleware(),
            new Fixture\BlankMiddleware(),
            new Fixture\BlankMiddleware(),
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
        $route = new Fixture\TestRoute();

        $middlewares = [
            new Fixture\BlankMiddleware(),
            new Fixture\BlankMiddleware(true),
            new Fixture\BlankMiddleware(),
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
    public function testHandleForUnallowedMethod() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest('GET', '/');

        // the given exception message should be tested through exceptions factory...
        $this->expectException(MethodNotAllowedException::class);

        try {
            $router->handle($request);
        } catch (MethodNotAllowedException $e) {
            $allowedMethods = array_merge(
                $routes[0]->getMethods(),
                $routes[1]->getMethods(),
                $routes[2]->getMethods()
            );

            // $this->assertSame('GET', $e->fromContext('method'));
            $this->assertSame($allowedMethods, $e->fromContext('allowed'));
            $this->assertSame($allowedMethods, $e->getAllowedMethods());

            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testHandleForUndefinedRoute() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest($routes[0]->getMethods()[0], '/');

        // the given exception message should be tested through exceptions factory...
        $this->expectException(RouteNotFoundException::class);

        try {
            $router->handle($request);
        } catch (RouteNotFoundException $e) {
            // $this->assertSame('/', $e->fromContext('uri'));

            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testProcess() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $fallback = new Fixture\BlankRequestHandler();

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
    public function testProcessForUnallowedMethod() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest('GET', '/');

        $fallback = new Fixture\BlankRequestHandler();

        $router->process($request, $fallback);

        $this->assertInstanceOf(
            MethodNotAllowedException::class,
            $fallback->getAttribute(Router::ATTR_NAME_FOR_ROUTING_ERROR)
        );

        $this->assertTrue($fallback->isRunned());
    }

    /**
     * @return void
     */
    public function testProcessForUndefinedRoute() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest($routes[0]->getMethods()[0], '/');

        $fallback = new Fixture\BlankRequestHandler();

        $router->process($request, $fallback);

        $this->assertInstanceOf(
            RouteNotFoundException::class,
            $fallback->getAttribute(Router::ATTR_NAME_FOR_ROUTING_ERROR)
        );

        $this->assertTrue($fallback->isRunned());
    }

    /**
     * @return void
     */
    public function testLoad() : void
    {
        $router = new Router();

        $expectedRoutes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $loader = $this->createMock(LoaderInterface::class);

        $loader->method('load')->willReturn(
            new RouteCollection(...$expectedRoutes)
        );

        $router->load($loader);

        $this->assertSame($expectedRoutes, $router->getRoutes());
    }

    /**
     * @return void
     */
    public function testMultipleLoad() : void
    {
        $router = new Router();

        $expectedRoutes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $loaders = [
            $this->createMock(LoaderInterface::class),
            $this->createMock(LoaderInterface::class),
            $this->createMock(LoaderInterface::class),
        ];

        $loaders[0]->method('load')->willReturn(
            new RouteCollection($expectedRoutes[0])
        );

        $loaders[1]->method('load')->willReturn(
            new RouteCollection($expectedRoutes[1])
        );

        $loaders[2]->method('load')->willReturn(
            new RouteCollection($expectedRoutes[2])
        );

        $router->load(...$loaders);

        $this->assertSame($expectedRoutes, $router->getRoutes());
    }
}
