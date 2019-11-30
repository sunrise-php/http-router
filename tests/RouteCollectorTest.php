<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionGroupActionInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\RouteFactoryInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Router;

/**
 * RouteCollectorTest
 */
class RouteCollectorTest extends TestCase
{

    /**
     * @return void
     */
    public function testDefaultCollection() : void
    {
        $collector = new RouteCollector();

        $this->assertInstanceOf(RouteCollectionInterface::class, $collector->getCollection());
    }

    /**
     * @return void
     */
    public function testCollectionFactory() : void
    {
        $expectedCollection = new RouteCollection();

        $collectionFactory = $this->createMock(RouteCollectionFactoryInterface::class);
        $collectionFactory->method('createCollection')->willReturn($expectedCollection);

        $collector = new RouteCollector($collectionFactory);

        $this->assertSame($expectedCollection, $collector->getCollection());
    }

    /**
     * @return void
     */
    public function testRouteFactory() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $collector = new RouteCollector(null, $routeFactory);
        $builtRoute = $collector->route('test', '/test', ['GET'], new Fixture\BlankRequestHandler());

        $this->assertSame($expectedRoute, $builtRoute);
    }

    /**
     * @return void
     */
    public function testRouteFactoryThroughHeadMethod() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $collector = new RouteCollector(null, $routeFactory);
        $builtRoute = $collector->head('test', '/test', new Fixture\BlankRequestHandler());

        $this->assertSame($expectedRoute, $builtRoute);
    }

    /**
     * @return void
     */
    public function testRouteFactoryThroughGetMethod() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $collector = new RouteCollector(null, $routeFactory);
        $builtRoute = $collector->get('test', '/test', new Fixture\BlankRequestHandler());

        $this->assertSame($expectedRoute, $builtRoute);
    }

    /**
     * @return void
     */
    public function testRouteFactoryThroughPostMethod() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $collector = new RouteCollector(null, $routeFactory);
        $builtRoute = $collector->post('test', '/test', new Fixture\BlankRequestHandler());

        $this->assertSame($expectedRoute, $builtRoute);
    }

    /**
     * @return void
     */
    public function testRouteFactoryThroughPutMethod() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $collector = new RouteCollector(null, $routeFactory);
        $builtRoute = $collector->put('test', '/test', new Fixture\BlankRequestHandler());

        $this->assertSame($expectedRoute, $builtRoute);
    }

    /**
     * @return void
     */
    public function testRouteFactoryThroughPatchMethod() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $collector = new RouteCollector(null, $routeFactory);
        $builtRoute = $collector->patch('test', '/test', new Fixture\BlankRequestHandler());

        $this->assertSame($expectedRoute, $builtRoute);
    }

    /**
     * @return void
     */
    public function testRouteFactoryThroughDeleteMethod() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $collector = new RouteCollector(null, $routeFactory);
        $builtRoute = $collector->delete('test', '/test', new Fixture\BlankRequestHandler());

        $this->assertSame($expectedRoute, $builtRoute);
    }

    /**
     * @return void
     */
    public function testRouteFactoryThroughPurgeMethod() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $collector = new RouteCollector(null, $routeFactory);
        $builtRoute = $collector->purge('test', '/test', new Fixture\BlankRequestHandler());

        $this->assertSame($expectedRoute, $builtRoute);
    }

    /**
     * @return void
     */
    public function testFactoriesTransferringDuringGrouping() : void
    {
        $collectionFactory = $this->createMock(RouteCollectionFactoryInterface::class);
        $routeFactory = $this->createMock(RouteFactoryInterface::class);

        $collectionFactory->expects($this->exactly(4))
            ->method('createCollection')
            ->willReturn(new RouteCollection());

        $routeFactory->expects($this->exactly(1))
            ->method('createRoute')
            ->willReturn(new Fixture\TestRoute());

        $collector = new RouteCollector(
            $collectionFactory,
            $routeFactory
        );

        $collector->group(function ($collector) {
            $collector->group(function ($collector) {
                $collector->group(function ($collector) {
                    $collector->route('test', '/test', ['GET'], new Fixture\BlankRequestHandler());
                });
            });
        });
    }

    /**
     * @return void
     */
    public function testRoute() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeMethods = Fixture\TestRoute::getTestRouteMethods();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $collector = new RouteCollector();

        $route = $collector->route(
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

        // default property values...
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testRouteWithOptionalParams() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeMethods = Fixture\TestRoute::getTestRouteMethods();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $collector = new RouteCollector();

        $route = $collector->route(
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
    public function testHead() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $collector = new RouteCollector();

        $route = $collector->head(
            $routeName,
            $routePath,
            $routeRequestHandler
        );

        $this->assertInstanceOf(RouteInterface::class, $route);

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_HEAD], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());

        // default property values...
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testHeadWithOptionalParams() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $collector = new RouteCollector();

        $route = $collector->head(
            $routeName,
            $routePath,
            $routeRequestHandler,
            $routeMiddlewares,
            $routeAttributes
        );

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_HEAD], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame($routeMiddlewares, $route->getMiddlewares());
        $this->assertSame($routeAttributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testGet() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $collector = new RouteCollector();

        $route = $collector->get(
            $routeName,
            $routePath,
            $routeRequestHandler
        );

        $this->assertInstanceOf(RouteInterface::class, $route);

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_GET], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());

        // default property values...
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testGetWithOptionalParams() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $collector = new RouteCollector();

        $route = $collector->get(
            $routeName,
            $routePath,
            $routeRequestHandler,
            $routeMiddlewares,
            $routeAttributes
        );

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_GET], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame($routeMiddlewares, $route->getMiddlewares());
        $this->assertSame($routeAttributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testPost() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $collector = new RouteCollector();

        $route = $collector->post(
            $routeName,
            $routePath,
            $routeRequestHandler
        );

        $this->assertInstanceOf(RouteInterface::class, $route);

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_POST], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());

        // default property values...
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testPostWithOptionalParams() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $collector = new RouteCollector();

        $route = $collector->post(
            $routeName,
            $routePath,
            $routeRequestHandler,
            $routeMiddlewares,
            $routeAttributes
        );

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_POST], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame($routeMiddlewares, $route->getMiddlewares());
        $this->assertSame($routeAttributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testPut() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $collector = new RouteCollector();

        $route = $collector->put(
            $routeName,
            $routePath,
            $routeRequestHandler
        );

        $this->assertInstanceOf(RouteInterface::class, $route);

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_PUT], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());

        // default property values...
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testPutWithOptionalParams() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $collector = new RouteCollector();

        $route = $collector->put(
            $routeName,
            $routePath,
            $routeRequestHandler,
            $routeMiddlewares,
            $routeAttributes
        );

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_PUT], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame($routeMiddlewares, $route->getMiddlewares());
        $this->assertSame($routeAttributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testPatch() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $collector = new RouteCollector();

        $route = $collector->patch(
            $routeName,
            $routePath,
            $routeRequestHandler
        );

        $this->assertInstanceOf(RouteInterface::class, $route);

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_PATCH], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());

        // default property values...
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testPatchWithOptionalParams() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $collector = new RouteCollector();

        $route = $collector->patch(
            $routeName,
            $routePath,
            $routeRequestHandler,
            $routeMiddlewares,
            $routeAttributes
        );

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_PATCH], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame($routeMiddlewares, $route->getMiddlewares());
        $this->assertSame($routeAttributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testDelete() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $collector = new RouteCollector();

        $route = $collector->delete(
            $routeName,
            $routePath,
            $routeRequestHandler
        );

        $this->assertInstanceOf(RouteInterface::class, $route);

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_DELETE], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());

        // default property values...
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testDeleteWithOptionalParams() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $collector = new RouteCollector();

        $route = $collector->delete(
            $routeName,
            $routePath,
            $routeRequestHandler,
            $routeMiddlewares,
            $routeAttributes
        );

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_DELETE], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame($routeMiddlewares, $route->getMiddlewares());
        $this->assertSame($routeAttributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testPurge() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();

        $collector = new RouteCollector();

        $route = $collector->purge(
            $routeName,
            $routePath,
            $routeRequestHandler
        );

        $this->assertInstanceOf(RouteInterface::class, $route);

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_PURGE], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());

        // default property values...
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testPurgeWithOptionalParams() : void
    {
        $routeName = Fixture\TestRoute::getTestRouteName();
        $routePath = Fixture\TestRoute::getTestRoutePath();
        $routeRequestHandler = Fixture\TestRoute::getTestRouteRequestHandler();
        $routeMiddlewares = Fixture\TestRoute::getTestRouteMiddlewares();
        $routeAttributes = Fixture\TestRoute::getTestRouteAttributes();

        $collector = new RouteCollector();

        $route = $collector->purge(
            $routeName,
            $routePath,
            $routeRequestHandler,
            $routeMiddlewares,
            $routeAttributes
        );

        $this->assertSame($routeName, $route->getName());
        $this->assertSame($routePath, $route->getPath());
        $this->assertSame([Router::METHOD_PURGE], $route->getMethods());
        $this->assertSame($routeRequestHandler, $route->getRequestHandler());
        $this->assertSame($routeMiddlewares, $route->getMiddlewares());
        $this->assertSame($routeAttributes, $route->getAttributes());
    }

    /**
     * @return void
     */
    public function testGroup() : void
    {
        $collector = new RouteCollector();
        $collector->get('home', '/', new Fixture\BlankRequestHandler());

        $collector->group(function ($group) {
            $group->get('api.ping', '/ping', new Fixture\BlankRequestHandler());

            $group->group(function ($group) {
                $group->group(function ($group) {
                    $group->post('api.section.create', '/create', new Fixture\BlankRequestHandler());
                    $group->patch('api.section.update', '/update/{id}', new Fixture\BlankRequestHandler());
                })->addPrefix('/section');

                $group->group(function ($group) {
                    $group->post('api.product.create', '/create', new Fixture\BlankRequestHandler());
                    $group->patch('api.product.update', '/update/{id}', new Fixture\BlankRequestHandler());
                })
                ->addPrefix('/product');
            })
            ->addPrefix('/v1');
        })
        ->addPrefix('/api');

        $collector->get('about-us', '/about-us', new Fixture\BlankRequestHandler());

        $routes = $collector->getCollection()->all();

        $this->assertContains([
            'name' => 'home',
            'path' => '/',
            'methods' => [Router::METHOD_GET],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'api.ping',
            'path' => '/api/ping',
            'methods' => [Router::METHOD_GET],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'api.section.create',
            'path' => '/api/v1/section/create',
            'methods' => [Router::METHOD_POST],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'api.section.update',
            'path' => '/api/v1/section/update/{id}',
            'methods' => [Router::METHOD_PATCH],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'api.product.create',
            'path' => '/api/v1/product/create',
            'methods' => [Router::METHOD_POST],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'api.product.update',
            'path' => '/api/v1/product/update/{id}',
            'methods' => [Router::METHOD_PATCH],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'about-us',
            'path' => '/about-us',
            'methods' => [Router::METHOD_GET],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));
    }
}
