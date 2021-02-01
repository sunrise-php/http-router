<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * Import functions
 */
use function array_merge;

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
        $this->assertInstanceOf(RequestHandlerInterface::class, $route);

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame($routeMethods, $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());

        // default property values...
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
    public function testAddPrefix() : void
    {
        $route = new Fixture\TestRoute();
        $pathPrefix = '/foo';
        $expectedPath = $pathPrefix . $route->getPath();

        $this->assertSame($route, $route->addPrefix($pathPrefix));
        $this->assertSame($expectedPath, $route->getPath());
    }

    /**
     * @return void
     */
    public function testAddSuffix() : void
    {
        $route = new Fixture\TestRoute();
        $pathSuffix = '.foo';
        $expectedPath = $route->getPath() . $pathSuffix;

        $this->assertSame($route, $route->addSuffix($pathSuffix));
        $this->assertSame($expectedPath, $route->getPath());
    }

    /**
     * @return void
     */
    public function testAddMethod() : void
    {
        $route = new Fixture\TestRoute();
        $extraMethods = Fixture\TestRoute::getTestRouteMethods();
        $expectedMethods = array_merge($route->getMethods(), $extraMethods);

        $this->assertSame($route, $route->addMethod(...$extraMethods));
        $this->assertSame($expectedMethods, $route->getMethods());
    }

    /**
     * @return void
     */
    public function testAddMiddleware() : void
    {
        $route = new Fixture\TestRoute();
        $extraMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $expectedMiddlewares = array_merge($route->getMiddlewares(), $extraMiddlewares);

        $this->assertSame($route, $route->addMiddleware(...$extraMiddlewares));
        $this->assertSame($expectedMiddlewares, $route->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testWithAddedAttributes() : void
    {
        $route = new Fixture\TestRoute();
        $extraAttributes = Fixture\TestRoute::getTestRouteAttributes();
        $expectedAttributes = $route->getAttributes() + $extraAttributes;
        $routeClone = $route->withAddedAttributes($extraAttributes);

        $this->assertInstanceOf(RouteInterface::class, $routeClone);
        $this->assertNotSame($route, $routeClone);
        $this->assertSame($expectedAttributes, $routeClone->getAttributes());
    }

    /**
     * @return void
     */
    public function testSetLowercasedMethods() : void
    {
        $route = new Fixture\TestRoute();
        $expectedMethods = ['FOO', 'BAR'];

        $route->setMethods('foo', 'bar');
        $this->assertSame($expectedMethods, $route->getMethods());
    }

    /**
     * @return void
     */
    public function testAddSlashEndingPrefix() : void
    {
        $route = new Fixture\TestRoute();
        $expectedPath = '/foo' . $route->getPath();

        $route->addPrefix('/foo/');
        $this->assertSame($expectedPath, $route->getPath());
    }

    /**
     * @return void
     */
    public function testAddLowercasedMethod() : void
    {
        $route = new Fixture\TestRoute();
        $expectedMethods = $route->getMethods();
        $expectedMethods[] = 'GET';
        $expectedMethods[] = 'POST';

        $route->addMethod('get', 'post');
        $this->assertSame($expectedMethods, $route->getMethods());
    }

    /**
     * @return void
     */
    public function testHandle() : void
    {
        $route = new Fixture\TestRoute();
        $route->handle((new ServerRequestFactory)->createServerRequest('GET', '/'));

        $this->assertTrue($route->getMiddlewares()[0]->isRunned());
        $this->assertTrue($route->getMiddlewares()[1]->isRunned());
        $this->assertTrue($route->getMiddlewares()[2]->isRunned());
        $this->assertTrue($route->getRequestHandler()->isRunned());

        $expectedAttributes = [
            Route::ATTR_NAME_FOR_ROUTE => $route,
            Route::ATTR_NAME_FOR_ROUTE_NAME => $route->getName(),
        ];

        $expectedAttributes += $route->getAttributes();

        $this->assertSame($expectedAttributes, $route->getRequestHandler()->getAttributes());
    }

    /**
     * @return void
     */
    public function testHandleWithBrokenMiddleware() : void
    {
        $route = new Fixture\TestRoute(Fixture\TestRoute::WITH_BROKEN_MIDDLEWARE);
        $route->handle((new ServerRequestFactory)->createServerRequest('GET', '/'));

        $this->assertTrue($route->getMiddlewares()[0]->isRunned());
        $this->assertTrue($route->getMiddlewares()[1]->isRunned());
        $this->assertFalse($route->getMiddlewares()[2]->isRunned());
        $this->assertFalse($route->getRequestHandler()->isRunned());
        $this->assertSame([], $route->getRequestHandler()->getAttributes());
    }

    /**
     * @return void
     */
    public function testSummary() : void
    {
        $route = new Fixture\TestRoute();
        $expectedSummary = 'foo bar';

        $this->assertSame('', $route->getSummary());
        $this->assertSame($route, $route->setSummary($expectedSummary));
        $this->assertSame($expectedSummary, $route->getSummary());
    }

    /**
     * @return void
     */
    public function testDescription() : void
    {
        $route = new Fixture\TestRoute();
        $expectedDescription = 'foo bar';

        $this->assertSame('', $route->getDescription());
        $this->assertSame($route, $route->setDescription($expectedDescription));
        $this->assertSame($expectedDescription, $route->getDescription());
    }

    /**
     * @return void
     */
    public function testTags() : void
    {
        $route = new Fixture\TestRoute();
        $expectedTags = ['foo', 'bar'];

        $this->assertSame([], $route->getTags());
        $this->assertSame($route, $route->setTags(...$expectedTags));
        $this->assertSame($expectedTags, $route->getTags());
    }

    /**
     * @return void
     *
     * @since 2.6.0
     */
    public function testHost() : void
    {
        $route = new Fixture\TestRoute();

        $this->assertSame(null, $route->getHost());

        $this->assertSame($route, $route->setHost('localhost'));
        $this->assertSame('localhost', $route->getHost());

        $this->assertSame($route, $route->setHost(null));
        $this->assertSame(null, $route->getHost());
    }
}
