<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;
use Sunrise\Http\Router\RouteInterface;

/**
 * RouteFactoryTest
 */
class RouteFactoryTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $factory = new RouteFactory();

        $this->assertInstanceOf(RouteFactoryInterface::class, $factory);
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
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $attributes = ['foo' => 'bar'];

        $route = (new RouteFactory)->createRoute(
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
}
