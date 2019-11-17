<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteInterface;
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

        $this->assertInstanceOf(RouteCollection::class, $router);
        $this->assertInstanceOf(MiddlewareInterface::class, $router);
        $this->assertInstanceOf(RequestHandlerInterface::class, $router);
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
        $router->addRoutes(...$routes);

        $this->assertSame($routes[0], $router->getRoute($routes[0]->getName()));
        $this->assertSame($routes[1], $router->getRoute($routes[1]->getName()));
        $this->assertSame($routes[2], $router->getRoute($routes[2]->getName()));
    }

    /**
     * @return void
     */
    public function testGetUndefinedRoute() : void
    {
        $router = new Router();

        $this->expectException(RouteNotFoundException::class);
        $router->getRoute('foo');
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
        $router->addRoutes(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest(
                $routes[2]->getMethods()[1],
                $routes[2]->getPath()
            );

        $foundRoute = $router->match($request);

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
        $router->addRoutes(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest('GET', '/');

        $this->expectException(MethodNotAllowedException::class);

        try {
            $router->match($request);
        } catch (MethodNotAllowedException $e) {
            $allowedMethods = array_merge(
                $routes[0]->getMethods(),
                $routes[1]->getMethods(),
                $routes[2]->getMethods()
            );

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
        $router->addRoutes(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest($routes[0]->getMethods()[0], '/');

        $this->expectException(RouteNotFoundException::class);
        $router->match($request);
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
        $router->addRoutes(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest(
                $routes[2]->getMethods()[1],
                $routes[2]->getPath()
            );

        $router->handle($request);

        $this->assertTrue($routes[2]->getRequestHandler()->isRunned());
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
        $router->addRoutes(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest('GET', '/');

        $this->expectException(MethodNotAllowedException::class);

        try {
            $router->handle($request);
        } catch (MethodNotAllowedException $e) {
            $allowedMethods = array_merge(
                $routes[0]->getMethods(),
                $routes[1]->getMethods(),
                $routes[2]->getMethods()
            );

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
        $router->addRoutes(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest($routes[0]->getMethods()[0], '/');

        $this->expectException(RouteNotFoundException::class);
        $router->handle($request);
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
        $router->addRoutes(...$routes);

        $request = (new ServerRequestFactory)
            ->createServerRequest(
                $routes[2]->getMethods()[1],
                $routes[2]->getPath()
            );

        $fallback = new Fixture\BlankRequestHandler();

        $router->process($request, $fallback);

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
        $router->addRoutes(...$routes);

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
        $router->addRoutes(...$routes);

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
     *
     * @todo This test needs to be improved...
     */
    public function testMatchPatterns() : void
    {
        $router = new Router();
        $router->get('test', '/{foo<[0-9]+>}/{bar<[a-zA-Z]+>}(/{baz<.*?>})', new Fixture\BlankRequestHandler());

        $route = $this->discoverRoute($router, 'GET', '/1990/Surgut/Tyumen');
        $this->assertEquals($route->getAttributes(), [
            'foo' => '1990',
            'bar' => 'Surgut',
            'baz' => 'Tyumen',
        ]);

        $route = $this->discoverRoute($router, 'GET', '/1990/Surgut/Tyumen/Moscow');
        $this->assertEquals($route->getAttributes(), [
            'foo' => '1990',
            'bar' => 'Surgut',
            'baz' => 'Tyumen/Moscow',
        ]);

        $route = $this->discoverRoute($router, 'GET', '/Oops/Surgut/Tyumen/Moscow');
        $this->assertNull($route);

        $route = $this->discoverRoute($router, 'GET', '/1990/2018/Moscow');
        $this->assertNull($route);
    }

    /**
     * @param Router $router
     * @param string $method
     * @param string $uri
     *
     * @return null|RouteInterface
     */
    private function discoverRoute(Router $router, string $method, string $uri) : ?RouteInterface
    {
        $request = (new ServerRequestFactory)
        ->createServerRequest($method, $uri);

        try {
            return $router->match($request);
        } catch (ExceptionInterface $e) {
            return null;
        }
    }
}
