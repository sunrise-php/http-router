<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\RouteInterface;

/**
 * RouteTest
 */
class RouteTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeMethods = Fixture\TestRoute::getTestRouteMethods();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $route = new Route(
            $routeName,
            $routePath,
            $routeMethods,
            $routeRequestHandler
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame($routeMethods, $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testConstructorWithOptionalParams() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeMethods = Fixture\TestRoute::getTestRouteMethods();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $route = new Route(
            $routeName,
            $routePath,
            $routeMethods,
            $routeRequestHandler,
            $routeMiddlewares,
            $routeAttributes
        );

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame($routeMethods, $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame($routeMiddlewares, $route->getMiddlewares());
        $this->assertSame($routeAttributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testSetName() : void
    {
        $route = new Fixture\TestRoute();
        $newRouteName = Fixture\TestRoute::getTestRouteName();

        $this->assertNotSame($route->getName(), $newRouteName);
        $this->assertSame($route, $route->setName($newRouteName));
        $this->assertSame($newRouteName, $route->getName());
    }

    /**
     * @return void
     */
    public function testSetPath() : void
    {
        $route = new Fixture\TestRoute();
        $newRoutePath = Fixture\TestRoute::getTestRoutePath();

        $this->assertNotSame($route->getPath(), $newRoutePath);
        $this->assertSame($route, $route->setPath($newRoutePath));
        $this->assertSame($newRoutePath, $route->getPath());
    }

    /**
     * @return void
     */
    public function testSetMethods() : void
    {
        $route = new Fixture\TestRoute();
        $newRouteMethods = Fixture\TestRoute::getTestRouteMethods();

        $this->assertNotSame($route->getMethods(), $newRouteMethods);
        $this->assertSame($route, $route->setMethods(...$newRouteMethods));
        $this->assertSame($newRouteMethods, $route->getMethods());
    }

    /**
     * @return void
     */
    public function testSetLowercasedMethods() : void
    {
        $route = new Fixture\TestRoute();
        $route->setMethods('foo', 'bar');

        $this->assertSame(['FOO', 'BAR'], $route->getMethods());
    }

    /**
     * @return void
     */
    public function testSetRequestHandler() : void
    {
        $route = new Fixture\TestRoute();
        $newRouteRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $this->assertNotSame($route->getRequestHandler(), $newRouteRequestHandler);
        $this->assertSame($route, $route->setRequestHandler($newRouteRequestHandler));
        $this->assertSame($newRouteRequestHandler, $route->getRequestHandler());
    }

    /**
     * @return void
     */
    public function testSetMiddlewares() : void
    {
        $route = new Fixture\TestRoute();
        $newRouteMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();

        $this->assertNotSame($route->getMiddlewares(), $newRouteMiddlewares);
        $this->assertSame($route, $route->setMiddlewares(...$newRouteMiddlewares));
        $this->assertSame($newRouteMiddlewares, $route->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testSetAttributes() : void
    {
        $route = new Fixture\TestRoute();
        $newRouteAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $this->assertNotSame($route->getAttributes(), $newRouteAttributes);
        $this->assertSame($route, $route->setAttributes($newRouteAttributes));
        $this->assertSame($newRouteAttributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testWithAttributes() : void
    {
        $route = new Fixture\TestRoute();
        $extraAttributes = Fixture\TestRoute::getTestRouteAttributes();
        $expectedAttributes = $route->getAttributes() + $extraAttributes;
        $routeClone = $route->withAttributes($extraAttributes);

        $this->assertInstanceOf(RouteInterface::class, $routeClone);
        $this->assertNotSame($route, $routeClone);
        $this->assertSame($expectedAttributes, $routeClone->getAttributes());
    }
}
