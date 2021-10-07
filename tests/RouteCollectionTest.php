<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\RouteCollectionInterface;

/**
 * Import functions
 */
use function array_merge;

/**
 * RouteCollectionTest
 */
class RouteCollectionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $collection = new RouteCollection();

        $this->assertInstanceOf(RouteCollectionInterface::class, $collection);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $collection = new RouteCollection();

        $this->assertSame([], $collection->all());
    }

    /**
     * @return void
     */
    public function testConstructorWithRoutes() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $collection = new RouteCollection(...$routes);

        $this->assertSame($routes, $collection->all());
    }

    /**
     * @return void
     */
    public function testAdd() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $collection = new RouteCollection();
        $collection->add(...$routes);

        $this->assertSame($routes, $collection->all());
    }

    /**
     * @return void
     */
    public function testGet() : void
    {
        $route = new Fixtures\Route();
        $collection = new RouteCollection();

        $this->assertNull($collection->get($route->getName()));

        $collection->add($route);

        $this->assertSame($route, $collection->get($route->getName()));
    }

    /**
     * @return void
     */
    public function testHas() : void
    {
        $route = new Fixtures\Route();
        $collection = new RouteCollection();

        $this->assertFalse($collection->has($route->getName()));

        $collection->add($route);

        $this->assertTrue($collection->has($route->getName()));
    }

    /**
     * @return void
     */
    public function testSetHost() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $collection = new RouteCollection(...$routes);
        $collection->setHost('google.com');

        $this->assertSame('google.com', $routes[0]->getHost());
        $this->assertSame('google.com', $routes[1]->getHost());
        $this->assertSame('google.com', $routes[2]->getHost());
    }

    /**
     * @return void
     */
    public function testAddPrefix() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $routes[0]->setPath('/foo');
        $routes[1]->setPath('/bar');
        $routes[2]->setPath('/baz');

        $collection = new RouteCollection(...$routes);
        $collection->addPrefix('/api');

        $this->assertSame('/api/foo', $routes[0]->getPath());
        $this->assertSame('/api/bar', $routes[1]->getPath());
        $this->assertSame('/api/baz', $routes[2]->getPath());
    }

    /**
     * @return void
     */
    public function testAddSuffix() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $routes[0]->setPath('/foo');
        $routes[1]->setPath('/bar');
        $routes[2]->setPath('/baz');

        $collection = new RouteCollection(...$routes);
        $collection->addSuffix('.json');

        $this->assertSame('/foo.json', $routes[0]->getPath());
        $this->assertSame('/bar.json', $routes[1]->getPath());
        $this->assertSame('/baz.json', $routes[2]->getPath());
    }

    /**
     * @return void
     */
    public function testAddMethod() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $routes[0]->setMethods('FOO');
        $routes[1]->setMethods('BAR');
        $routes[2]->setMethods('BAZ');

        $collection = new RouteCollection(...$routes);
        $collection->addMethod('QUX', 'QUUX');

        $this->assertSame(['FOO', 'QUX', 'QUUX'], $routes[0]->getMethods());
        $this->assertSame(['BAR', 'QUX', 'QUUX'], $routes[1]->getMethods());
        $this->assertSame(['BAZ', 'QUX', 'QUUX'], $routes[2]->getMethods());
    }

    /**
     * @return void
     */
    public function testAddMiddleware() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $middlewares = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $additionals = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $routes[0]->setMiddlewares(...$middlewares);
        $routes[1]->setMiddlewares(...$middlewares);
        $routes[2]->setMiddlewares(...$middlewares);

        $collection = new RouteCollection(...$routes);
        $collection->addMiddleware(...$additionals);

        $this->assertSame(array_merge($middlewares, $additionals), $routes[0]->getMiddlewares());
        $this->assertSame(array_merge($middlewares, $additionals), $routes[1]->getMiddlewares());
        $this->assertSame(array_merge($middlewares, $additionals), $routes[2]->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testAppendMiddleware() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $middlewares = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $additionals = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $routes[0]->setMiddlewares(...$middlewares);
        $routes[1]->setMiddlewares(...$middlewares);
        $routes[2]->setMiddlewares(...$middlewares);

        $collection = new RouteCollection(...$routes);
        $collection->appendMiddleware(...$additionals);

        $this->assertSame(array_merge($middlewares, $additionals), $routes[0]->getMiddlewares());
        $this->assertSame(array_merge($middlewares, $additionals), $routes[1]->getMiddlewares());
        $this->assertSame(array_merge($middlewares, $additionals), $routes[2]->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testPrependMiddleware() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $middlewares = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $additionals = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $routes[0]->setMiddlewares(...$middlewares);
        $routes[1]->setMiddlewares(...$middlewares);
        $routes[2]->setMiddlewares(...$middlewares);

        $collection = new RouteCollection(...$routes);
        $collection->prependMiddleware(...$additionals);

        $this->assertSame(array_merge($additionals, $middlewares), $routes[0]->getMiddlewares());
        $this->assertSame(array_merge($additionals, $middlewares), $routes[1]->getMiddlewares());
        $this->assertSame(array_merge($additionals, $middlewares), $routes[2]->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testUnshiftMiddleware() : void
    {
        $routes = [
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $middlewares = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $additionals = [
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
            new Fixtures\Middlewares\BlankMiddleware(),
        ];

        $routes[0]->setMiddlewares(...$middlewares);
        $routes[1]->setMiddlewares(...$middlewares);
        $routes[2]->setMiddlewares(...$middlewares);

        $collection = new RouteCollection(...$routes);
        $collection->unshiftMiddleware(...$additionals);

        $this->assertSame(array_merge($additionals, $middlewares), $routes[0]->getMiddlewares());
        $this->assertSame(array_merge($additionals, $middlewares), $routes[1]->getMiddlewares());
        $this->assertSame(array_merge($additionals, $middlewares), $routes[2]->getMiddlewares());
    }
}
