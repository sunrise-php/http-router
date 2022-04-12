<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
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
        $name = 'foo';
        $path = '/foo';
        $methods = ['GET', 'POST'];
        $handler = new Fixtures\Controllers\BlankController();
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $attributes = ['foo' => 'bar'];

        $route = new Route(
            $name,
            $path,
            $methods,
            $handler,
            $middlewares,
            $attributes
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($name, $route->getName());
        $this->assertSame($path, $route->getPath());
        $this->assertSame($methods, $route->getMethods());
        $this->assertSame($handler, $route->getRequestHandler());
        $this->assertSame($middlewares, $route->getMiddlewares());
        $this->assertSame($attributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testSetName() : void
    {
        $route = new Fixtures\Route();
        $name = 'foo';
        $this->assertNotSame($route->getName(), $name);
        $this->assertSame($route, $route->setName($name));
        $this->assertSame($name, $route->getName());
    }

    /**
     * @return void
     */
    public function testSetHost() : void
    {
        $route = new Fixtures\Route();
        $host = 'localhost';
        $this->assertNull($route->getHost());
        $this->assertSame($route, $route->setHost($host));
        $this->assertSame($host, $route->getHost());
    }

    /**
     * @return void
     */
    public function testSetPath() : void
    {
        $route = new Fixtures\Route();
        $path = '/foo';
        $this->assertNotSame($route->getPath(), $path);
        $this->assertSame($route, $route->setPath($path));
        $this->assertSame($path, $route->getPath());
    }

    /**
     * @return void
     */
    public function testSetMethods() : void
    {
        $route = new Fixtures\Route();
        $methods = ['GET', 'POST'];
        $this->assertNotSame($route->getMethods(), $methods);
        $this->assertSame($route, $route->setMethods(...$methods));
        $this->assertSame($methods, $route->getMethods());
    }

    /**
     * @return void
     */
    public function testSetRequestHandler() : void
    {
        $route = new Fixtures\Route();
        $handler = new Fixtures\Controllers\BlankController();
        $this->assertNotSame($route->getRequestHandler(), $handler);
        $this->assertSame($route, $route->setRequestHandler($handler));
        $this->assertSame($handler, $route->getRequestHandler());
    }

    /**
     * @return void
     */
    public function testSetMiddlewares() : void
    {
        $route = new Fixtures\Route();
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $this->assertNotSame($route->getMiddlewares(), $middlewares);
        $this->assertSame($route, $route->setMiddlewares(...$middlewares));
        $this->assertSame($middlewares, $route->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testSetAttributes() : void
    {
        $route = new Fixtures\Route();
        $attributes = ['foo' => 'bar'];
        $this->assertSame([], $route->getAttributes());
        $this->assertSame($route, $route->setAttributes($attributes));
        $this->assertSame($attributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testSetSummary() : void
    {
        $route = new Fixtures\Route();
        $summary = 'foo bar';
        $this->assertSame('', $route->getSummary());
        $this->assertSame($route, $route->setSummary($summary));
        $this->assertSame($summary, $route->getSummary());
    }

    /**
     * @return void
     */
    public function testSetDescription() : void
    {
        $route = new Fixtures\Route();
        $description = 'foo bar';
        $this->assertSame('', $route->getDescription());
        $this->assertSame($route, $route->setDescription($description));
        $this->assertSame($description, $route->getDescription());
    }

    /**
     * @return void
     */
    public function testSetTags() : void
    {
        $route = new Fixtures\Route();
        $tags = ['foo', 'bar'];
        $this->assertSame([], $route->getTags());
        $this->assertSame($route, $route->setTags(...$tags));
        $this->assertSame($tags, $route->getTags());
    }

    /**
     * @return void
     */
    public function testAddPrefix() : void
    {
        $route = new Fixtures\Route();
        $route->setPath('/bar');
        $prefix = '/foo';
        $expected = $prefix . $route->getPath();
        $this->assertSame($route, $route->addPrefix($prefix));
        $this->assertSame($expected, $route->getPath());
    }

    /**
     * @return void
     */
    public function testAddSuffix() : void
    {
        $route = new Fixtures\Route();
        $route->setPath('/foo');
        $suffix = '.bar';
        $expected = $route->getPath() . $suffix;
        $this->assertSame($route, $route->addSuffix($suffix));
        $this->assertSame($expected, $route->getPath());
    }

    /**
     * @return void
     */
    public function testAddMethod() : void
    {
        $route = new Fixtures\Route();
        $route->setMethods('FOO');
        $methods = ['BAR', 'BAZ'];
        $expected = array_merge($route->getMethods(), $methods);
        $this->assertSame($route, $route->addMethod(...$methods));
        $this->assertSame($expected, $route->getMethods());
    }

    /**
     * @return void
     */
    public function testAddMiddleware() : void
    {
        $route = new Fixtures\Route();
        $route->setMiddlewares(new Fixtures\Middlewares\BlankMiddleware());
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $expected = array_merge($route->getMiddlewares(), $middlewares);
        $this->assertSame($route, $route->addMiddleware(...$middlewares));
        $this->assertSame($expected, $route->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testWithAddedAttributes() : void
    {
        $route = new Fixtures\Route();
        $route->setAttributes(['foo' => 'bar']);
        $attributes = ['bar' => 'baz'];
        $expected = $route->getAttributes() + $attributes;
        $clone = $route->withAddedAttributes($attributes);
        $this->assertNotSame($expected, $route->getAttributes());
        $this->assertInstanceOf(RouteInterface::class, $clone);
        $this->assertSame($expected, $clone->getAttributes());
        $this->assertNotSame($route, $clone);
    }

    /**
     * @return void
     */
    public function testAddSlashEndingPrefix() : void
    {
        $route = new Fixtures\Route();
        $route->setPath('/bar');
        $route->addPrefix('/foo/');
        $this->assertSame('/foo/bar', $route->getPath());
    }

    /**
     * @return void
     */
    public function testSetLowercasedMethods() : void
    {
        $route = new Fixtures\Route();
        $route->setMethods('foo', 'bar');
        $this->assertSame(['FOO', 'BAR'], $route->getMethods());
    }

    /**
     * @return void
     */
    public function testAddLowercasedMethod() : void
    {
        $route = new Fixtures\Route();
        $route->setMethods(...[]); // clear previous methods...
        $route->addMethod('foo', 'bar');
        $this->assertSame(['FOO', 'BAR'], $route->getMethods());
    }

    /**
     * @return void
     */
    public function testRun() : void
    {
        $route = new Fixtures\Route();
        $route->handle((new ServerRequestFactory)->createServerRequest('GET', '/'));

        $this->assertTrue($route->getRequestHandler()->isRunned());

        $this->assertSame([
            Route::ATTR_NAME_FOR_ROUTE => $route,
            Route::ATTR_NAME_FOR_ROUTE_NAME => $route->getName(),
        ], $route->getRequestHandler()->getRequest()->getAttributes());
    }

    /**
     * @return void
     */
    public function testRunWithAttributes() : void
    {
        $route = new Fixtures\Route();
        $route->setAttributes(['foo' => 'bar']);
        $route->handle((new ServerRequestFactory)->createServerRequest('GET', '/'));

        $this->assertTrue($route->getRequestHandler()->isRunned());

        $this->assertSame([
            Route::ATTR_NAME_FOR_ROUTE => $route,
            Route::ATTR_NAME_FOR_ROUTE_NAME => $route->getName(),
        ] + $route->getAttributes(), $route->getRequestHandler()->getRequest()->getAttributes());
    }

    /**
     * @return void
     */
    public function testRunWithMiddlewares() : void
    {
        $route = new Fixtures\Route();
        $route->addMiddleware(new Fixtures\Middlewares\BlankMiddleware());
        $route->addMiddleware(new Fixtures\Middlewares\BlankMiddleware());
        $route->addMiddleware(new Fixtures\Middlewares\BlankMiddleware());
        $route->handle((new ServerRequestFactory)->createServerRequest('GET', '/'));

        $this->assertTrue($route->getMiddlewares()[0]->isRunned());
        $this->assertTrue($route->getMiddlewares()[1]->isRunned());
        $this->assertTrue($route->getMiddlewares()[2]->isRunned());
        $this->assertTrue($route->getRequestHandler()->isRunned());

        $attributes = [
            Route::ATTR_NAME_FOR_ROUTE => $route,
            Route::ATTR_NAME_FOR_ROUTE_NAME => $route->getName(),
        ];

        $this->assertSame($attributes, $route->getMiddlewares()[0]->getRequest()->getAttributes());
        $this->assertSame($attributes, $route->getMiddlewares()[1]->getRequest()->getAttributes());
        $this->assertSame($attributes, $route->getMiddlewares()[2]->getRequest()->getAttributes());
        $this->assertSame($attributes, $route->getRequestHandler()->getRequest()->getAttributes());
    }

    /**
     * @return void
     */
    public function testRunWithBrokenMiddleware() : void
    {
        $route = new Fixtures\Route();
        $route->addMiddleware(new Fixtures\Middlewares\BlankMiddleware());
        $route->addMiddleware(new Fixtures\Middlewares\BlankMiddleware(true));
        $route->addMiddleware(new Fixtures\Middlewares\BlankMiddleware());
        $route->handle((new ServerRequestFactory)->createServerRequest('GET', '/'));

        $this->assertTrue($route->getMiddlewares()[0]->isRunned());
        $this->assertTrue($route->getMiddlewares()[1]->isRunned());
        $this->assertFalse($route->getMiddlewares()[2]->isRunned());
        $this->assertFalse($route->getRequestHandler()->isRunned());

        $attributes = [
            Route::ATTR_NAME_FOR_ROUTE => $route,
            Route::ATTR_NAME_FOR_ROUTE_NAME => $route->getName(),
        ];

        $this->assertSame($attributes, $route->getMiddlewares()[0]->getRequest()->getAttributes());
        $this->assertSame($attributes, $route->getMiddlewares()[1]->getRequest()->getAttributes());
        $this->assertNull($route->getMiddlewares()[2]->getRequest());
        $this->assertNull($route->getRequestHandler()->getRequest());
    }

    /**
     * @return void
     */
    public function testGetClassHolder() : void
    {
        $class = new Fixtures\Controllers\BlankController();

        $route = new Route('foo', '/foo', [], $class);
        $holder = $route->getHolder();

        $this->assertInstanceOf(\ReflectionClass::class, $holder);
        $this->assertSame(\get_class($class), $holder->getName());
    }

    /**
     * @return void
     */
    public function testGetClosureHolder() : void
    {
        $callback = function () {
        };

        $route = new Route('foo', '/foo', [], new CallableRequestHandler($callback));
        $holder = $route->getHolder();

        $this->assertInstanceOf(\ReflectionFunction::class, $holder);
        $this->assertSame($callback, $holder->getClosure());
    }

    /**
     * @return void
     */
    public function testGetMethodHolder() : void
    {
        $class = new Fixtures\Controllers\BlankController();
        $method = '__invoke';

        $route = new Route('foo', '/foo', [], new CallableRequestHandler([$class, $method]));
        $holder = $route->getHolder();

        $this->assertInstanceOf(\ReflectionMethod::class, $holder);
        $this->assertSame(\get_class($class), $holder->getDeclaringClass()->getName());
        $this->assertSame($method, $holder->getName());
    }
}
