<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Router;

/**
 * RouteCollectorTest
 */
class RouteCollectorTest extends TestCase
{
    use Fixtures\ContainerAwareTrait;

    /**
     * @return void
     */
    public function testContainer() : void
    {
        $container = $this->getContainer();

        $collector = new RouteCollector();
        $this->assertNull($collector->getContainer());

        $collector->setContainer($container);
        $this->assertSame($container, $collector->getContainer());

        $collector->setContainer(null);
        $this->assertNull($collector->getContainer());
    }

    /**
     * @return void
     */
    public function testCreateRoute() : void
    {
        $name = 'foo';
        $path = '/foo';
        $methods = ['GET', 'POST'];
        $handler = new Fixtures\Controllers\BlankController();
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $attributes = [];
        $attributes['foo'] = 'bar';

        $collector = new RouteCollector();

        $route = $collector->route(
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
        $this->assertTrue($collector->getCollection()->has($route->getName()));
        $this->assertSame($route, $collector->getCollection()->get($route->getName()));
    }

    /**
     * @return void
     */
    public function testCreateHeadRoute() : void
    {
        $name = 'foo';
        $path = '/foo';
        $handler = new Fixtures\Controllers\BlankController();
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $attributes = [];
        $attributes['foo'] = 'bar';

        $collector = new RouteCollector();

        $route = $collector->head(
            $name,
            $path,
            $handler,
            $middlewares,
            $attributes
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($name, $route->getName());
        $this->assertSame($path, $route->getPath());
        $this->assertSame([Router::METHOD_HEAD], $route->getMethods());
        $this->assertSame($handler, $route->getRequestHandler());
        $this->assertSame($middlewares, $route->getMiddlewares());
        $this->assertSame($attributes, $route->getAttributes());
        $this->assertTrue($collector->getCollection()->has($route->getName()));
        $this->assertSame($route, $collector->getCollection()->get($route->getName()));
    }

    /**
     * @return void
     */
    public function testCreateGetRoute() : void
    {
        $name = 'foo';
        $path = '/foo';
        $handler = new Fixtures\Controllers\BlankController();
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $attributes = [];
        $attributes['foo'] = 'bar';

        $collector = new RouteCollector();

        $route = $collector->get(
            $name,
            $path,
            $handler,
            $middlewares,
            $attributes
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($name, $route->getName());
        $this->assertSame($path, $route->getPath());
        $this->assertSame([Router::METHOD_GET], $route->getMethods());
        $this->assertSame($handler, $route->getRequestHandler());
        $this->assertSame($middlewares, $route->getMiddlewares());
        $this->assertSame($attributes, $route->getAttributes());
        $this->assertTrue($collector->getCollection()->has($route->getName()));
        $this->assertSame($route, $collector->getCollection()->get($route->getName()));
    }

    /**
     * @return void
     */
    public function testCreatePostRoute() : void
    {
        $name = 'foo';
        $path = '/foo';
        $handler = new Fixtures\Controllers\BlankController();
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $attributes = [];
        $attributes['foo'] = 'bar';

        $collector = new RouteCollector();

        $route = $collector->post(
            $name,
            $path,
            $handler,
            $middlewares,
            $attributes
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($name, $route->getName());
        $this->assertSame($path, $route->getPath());
        $this->assertSame([Router::METHOD_POST], $route->getMethods());
        $this->assertSame($handler, $route->getRequestHandler());
        $this->assertSame($middlewares, $route->getMiddlewares());
        $this->assertSame($attributes, $route->getAttributes());
        $this->assertTrue($collector->getCollection()->has($route->getName()));
        $this->assertSame($route, $collector->getCollection()->get($route->getName()));
    }

    /**
     * @return void
     */
    public function testCreatePutRoute() : void
    {
        $name = 'foo';
        $path = '/foo';
        $handler = new Fixtures\Controllers\BlankController();
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $attributes = [];
        $attributes['foo'] = 'bar';

        $collector = new RouteCollector();

        $route = $collector->put(
            $name,
            $path,
            $handler,
            $middlewares,
            $attributes
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($name, $route->getName());
        $this->assertSame($path, $route->getPath());
        $this->assertSame([Router::METHOD_PUT], $route->getMethods());
        $this->assertSame($handler, $route->getRequestHandler());
        $this->assertSame($middlewares, $route->getMiddlewares());
        $this->assertSame($attributes, $route->getAttributes());
        $this->assertTrue($collector->getCollection()->has($route->getName()));
        $this->assertSame($route, $collector->getCollection()->get($route->getName()));
    }

    /**
     * @return void
     */
    public function testCreatePatchRoute() : void
    {
        $name = 'foo';
        $path = '/foo';
        $handler = new Fixtures\Controllers\BlankController();
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $attributes = [];
        $attributes['foo'] = 'bar';

        $collector = new RouteCollector();

        $route = $collector->patch(
            $name,
            $path,
            $handler,
            $middlewares,
            $attributes
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($name, $route->getName());
        $this->assertSame($path, $route->getPath());
        $this->assertSame([Router::METHOD_PATCH], $route->getMethods());
        $this->assertSame($handler, $route->getRequestHandler());
        $this->assertSame($middlewares, $route->getMiddlewares());
        $this->assertSame($attributes, $route->getAttributes());
        $this->assertTrue($collector->getCollection()->has($route->getName()));
        $this->assertSame($route, $collector->getCollection()->get($route->getName()));
    }

    /**
     * @return void
     */
    public function testCreateDeleteRoute() : void
    {
        $name = 'foo';
        $path = '/foo';
        $handler = new Fixtures\Controllers\BlankController();
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $attributes = [];
        $attributes['foo'] = 'bar';

        $collector = new RouteCollector();

        $route = $collector->delete(
            $name,
            $path,
            $handler,
            $middlewares,
            $attributes
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($name, $route->getName());
        $this->assertSame($path, $route->getPath());
        $this->assertSame([Router::METHOD_DELETE], $route->getMethods());
        $this->assertSame($handler, $route->getRequestHandler());
        $this->assertSame($middlewares, $route->getMiddlewares());
        $this->assertSame($attributes, $route->getAttributes());
        $this->assertTrue($collector->getCollection()->has($route->getName()));
        $this->assertSame($route, $collector->getCollection()->get($route->getName()));
    }

    /**
     * @return void
     */
    public function testCreatePurgeRoute() : void
    {
        $name = 'foo';
        $path = '/foo';
        $handler = new Fixtures\Controllers\BlankController();
        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $attributes = [];
        $attributes['foo'] = 'bar';

        $collector = new RouteCollector();

        $route = $collector->purge(
            $name,
            $path,
            $handler,
            $middlewares,
            $attributes
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($name, $route->getName());
        $this->assertSame($path, $route->getPath());
        $this->assertSame([Router::METHOD_PURGE], $route->getMethods());
        $this->assertSame($handler, $route->getRequestHandler());
        $this->assertSame($middlewares, $route->getMiddlewares());
        $this->assertSame($attributes, $route->getAttributes());
        $this->assertTrue($collector->getCollection()->has($route->getName()));
        $this->assertSame($route, $collector->getCollection()->get($route->getName()));
    }

    /**
     * @return void
     */
    public function testGrouping() : void
    {
        $collector = new RouteCollector();

        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();

        $groupCollection = $collector->group(function ($group) use ($middlewares) {
            $this->assertInstanceOf(RouteCollector::class, $group);

            $group->get('foo', '/foo', new Fixtures\Controllers\BlankController());

            $group->group(function ($subgroup) {
                $subgroup->get('bar', '/bar', new Fixtures\Controllers\BlankController());

                $subgroup->group(function ($deepgroup) {
                    $deepgroup->get('baz', '/baz', new Fixtures\Controllers\BlankController());
                });
            }, $middlewares);
        });

        $this->assertInstanceOf(RouteCollectionInterface::class, $groupCollection);
        $this->assertTrue($collector->getCollection()->has('foo'));
        $this->assertSame([], $collector->getCollection()->get('foo')->getMiddlewares());
        $this->assertTrue($collector->getCollection()->has('bar'));
        $this->assertSame($middlewares, $collector->getCollection()->get('bar')->getMiddlewares());
        $this->assertTrue($collector->getCollection()->has('baz'));
        $this->assertSame($middlewares, $collector->getCollection()->get('baz')->getMiddlewares());
    }
}
