<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\RouteCollectionInterface;

/**
 * Import functions
 */
use function end;

/**
 * RouteCollectionTest
 */
class RouteCollectionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $collection = new RouteCollection();

        $this->assertInstanceOf(RouteCollectionInterface::class, $collection);
    }

    /**
     * @return void
     */
    public function testGetDefaultPrefix() : void
    {
        $collection = new RouteCollection();

        $this->assertNull($collection->getPrefix());
    }

    /**
     * @return void
     */
    public function testGetDefaultMiddlewares() : void
    {
        $collection = new RouteCollection();

        $this->assertSame([], $collection->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testGetDefaultRoutes() : void
    {
        $collection = new RouteCollection();

        $this->assertSame([], $collection->getRoutes());
    }

    /**
     * @return void
     */
    public function testSetPrefix() : void
    {
        $collection = new RouteCollection();

        $this->assertSame($collection, $collection->setPrefix('/foo'));
        $this->assertSame('/foo', $collection->getPrefix());

        // override prefix...
        $collection->setPrefix('/bar');
        $this->assertSame('/bar', $collection->getPrefix());

        // https://github.com/sunrise-php/http-router/issues/26
        $collection->setPrefix('/baz/');
        $this->assertSame('/baz', $collection->getPrefix());
    }

    /**
     * @return void
     */
    public function testAddMiddlewares() : void
    {
        $middlewares = [
            new Fixture\BlankMiddleware(),
            new Fixture\BlankMiddleware(),
        ];

        $collection = new RouteCollection();

        $this->assertSame($collection, $collection->addMiddlewares(...$middlewares));
        $this->assertSame($middlewares, $collection->getMiddlewares());

        // extending...
        $middlewares[] = new Fixture\BlankMiddleware();
        $collection->addMiddlewares(end($middlewares));
        $this->assertSame($middlewares, $collection->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testAddRoutes() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $collection = new RouteCollection();

        $this->assertSame($collection, $collection->addRoutes(...$routes));
        $this->assertSame($routes, $collection->getRoutes());

        // extending...
        $routes[] = new Fixture\TestRoute();
        $collection->addRoutes(end($routes));
        $this->assertSame($routes, $collection->getRoutes());
    }

    /**
     * @return void
     */
    public function testMakeRoute() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeMethods = Fixture\TestRoute::getTestRouteMethods();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $collection = new RouteCollection();

        $route = $collection->route(
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
        $this->assertSame([$route], $collection->getRoutes());
    }

    /**
     * @return void
     */
    public function testMakeRouteWithOptionalParams() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeMethods = Fixture\TestRoute::getTestRouteMethods();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $collection = new RouteCollection();

        $route = $collection->route(
            $routeName,
            $routePath,
            $routeMethods,
            $routeRequestHandler,
            $routeMiddlewares,
            $routeAttributes
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame($routeMethods, $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame($routeMiddlewares, $route->getMiddlewares());
        $this->assertSame($routeAttributes, $route->getAttributes());
        $this->assertSame([$route], $collection->getRoutes());
    }

    /**
     * @return void
     */
    public function testMakeRouteWithTransferringPrefix() : void
    {
        $collection = new RouteCollection();
        $collection->setPrefix('/api');

        $route = $collection->route('foo', '/foo', ['GET'], new Fixture\BlankRequestHandler());
        $this->assertSame('/api/foo', $route->getPath());
    }

    /**
     * @return void
     */
    public function testMakeRouteWithTransferringMiddlewares() : void
    {
        $middlewares = [new Fixture\BlankMiddleware()];

        $collection = new RouteCollection();
        $collection->addMiddlewares(...$middlewares);

        $route = $collection->route('foo', '/foo', ['GET'], new Fixture\BlankRequestHandler());
        $this->assertSame($middlewares, $route->getMiddlewares());

        // merging...
        $middlewares[] = new Fixture\BlankMiddleware();
        $route = $collection->route('foo', '/foo', ['GET'], new Fixture\BlankRequestHandler(), [end($middlewares)]);
        $this->assertSame($middlewares, $route->getMiddlewares());
    }

    /**
     * @return void
     *
     * @dataProvider makeVerbableRoutesDataProvider
     */
    public function testMakeVerbableRoutes(string $calledMethod, string $expectedHttpMethod) : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $collection = new RouteCollection();

        $route = $collection->{$calledMethod}(
            $routeName,
            $routePath,
            $routeRequestHandler
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([$expectedHttpMethod], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
        $this->assertSame([$route], $collection->getRoutes());
    }

    /**
     * @return void
     *
     * @dataProvider makeVerbableRoutesDataProvider
     */
    public function testMakeVerbableRoutesWithOptionalParams(string $calledMethod, string $expectedHttpMethod) : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $collection = new RouteCollection();

        $route = $collection->{$calledMethod}(
            $routeName,
            $routePath,
            $routeRequestHandler,
            $routeMiddlewares,
            $routeAttributes
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([$expectedHttpMethod], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame($routeMiddlewares, $route->getMiddlewares());
        $this->assertSame($routeAttributes, $route->getAttributes());
        $this->assertSame([$route], $collection->getRoutes());
    }

    /**
     * @return void
     *
     * @dataProvider makeVerbableRoutesDataProvider
     */
    public function testMakeVerbableRoutesWithTransferringPrefix(string $calledMethod) : void
    {
        $collection = new RouteCollection();
        $collection->setPrefix('/api');

        $route = $collection->{$calledMethod}('foo', '/foo', new Fixture\BlankRequestHandler());
        $this->assertSame('/api/foo', $route->getPath());
    }

    /**
     * @return void
     *
     * @dataProvider makeVerbableRoutesDataProvider
     */
    public function testMakeVerbableRoutesWithTransferringMiddlewares(string $calledMethod) : void
    {
        $middlewares = [new Fixture\BlankMiddleware()];

        $collection = new RouteCollection();
        $collection->addMiddlewares(...$middlewares);

        $route = $collection->{$calledMethod}('foo', '/foo', new Fixture\BlankRequestHandler());
        $this->assertSame($middlewares, $route->getMiddlewares());

        // merging...
        $middlewares[] = new Fixture\BlankMiddleware();
        $route = $collection->{$calledMethod}('foo', '/foo', new Fixture\BlankRequestHandler(), [end($middlewares)]);
        $this->assertSame($middlewares, $route->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testGroup() : void
    {
        $collection = new RouteCollection();
        $collection->addMiddlewares(new Fixture\NamedBlankMiddleware('main'));

        $collection->group('/', function ($group) {
            $group->addMiddlewares(new Fixture\NamedBlankMiddleware('group'));

            $group->group('/api', function ($group) {
                $group->addMiddlewares(new Fixture\NamedBlankMiddleware('api'));

                $group->group('/v1', function ($group) {
                    $group->addMiddlewares(new Fixture\NamedBlankMiddleware('v1'));
                    $group->get('ping.pong', '/ping', new Fixture\BlankRequestHandler());
                });

                $group->group('/v2', function ($group) {
                    $group->addMiddlewares(new Fixture\NamedBlankMiddleware('v2'));
                    $group->get('ping.pong', '/ping', new Fixture\BlankRequestHandler());
                });
            });
        });

        $collection->get('home', '/', new Fixture\BlankRequestHandler());

        $this->assertSame([
            [
                'name' => 'ping.pong',
                'path' => '/api/v1/ping',
                'methods' => ['GET'],
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
                'middlewares' => [
                    'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:main',
                    'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:group',
                    'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:api',
                    'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:v1',
                ],
                'attributes' => [],
            ],
            [
                'name' => 'ping.pong',
                'path' => '/api/v2/ping',
                'methods' => ['GET'],
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
                'middlewares' => [
                    'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:main',
                    'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:group',
                    'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:api',
                    'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:v2',
                ],
                'attributes' => [],
            ],
            [
                'name' => 'home',
                'path' => '/',
                'methods' => ['GET'],
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
                'middlewares' => [
                    'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:main',
                ],
                'attributes' => [],
            ],
        ], Fixture\Helper::routesToArray($collection->getRoutes()));
    }

    /**
     * @return array
     */
    public function makeVerbableRoutesDataProvider() : array
    {
        return [
            [
                'head',
                'HEAD',
            ],
            [
                'get',
                'GET',
            ],
            [
                'post',
                'POST',
            ],
            [
                'put',
                'PUT',
            ],
            [
                'patch',
                'PATCH',
            ],
            [
                'delete',
                'DELETE',
            ],
            [
                'purge',
                'PURGE',
            ],
        ];
    }
}
