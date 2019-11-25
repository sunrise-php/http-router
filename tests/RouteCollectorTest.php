<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteCollection;
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
    public function testGetDefaultCollection() : void
    {
        $collector = new RouteCollector();

        $this->assertInstanceOf(RouteCollectionInterface::class, $collector->getCollection());
    }

    /**
     * @return void
     */
    public function testGetSettedCollection() : void
    {
        $collection = new RouteCollection();

        $collector = new RouteCollector($collection);

        $this->assertSame($collection, $collector->getCollection());
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
    public function testRouteWithCustomRouteFactory() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $createdRoute = (new RouteCollector(new RouteCollection(), $routeFactory))->route(
            Fixture\TestRoute::getTestRouteName(),
            Fixture\TestRoute::getTestRoutePath(),
            Fixture\TestRoute::getTestRouteMethods(),
            Fixture\TestRoute::getTestRouteRequestHandler()
        );

        $this->assertSame($expectedRoute, $createdRoute);
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
    public function testHeadWithCustomRouteFactory() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $createdRoute = (new RouteCollector(new RouteCollection(), $routeFactory))->head(
            Fixture\TestRoute::getTestRouteName(),
            Fixture\TestRoute::getTestRoutePath(),
            Fixture\TestRoute::getTestRouteRequestHandler()
        );

        $this->assertSame($expectedRoute, $createdRoute);
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
    public function testGetWithCustomRouteFactory() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $createdRoute = (new RouteCollector(new RouteCollection(), $routeFactory))->get(
            Fixture\TestRoute::getTestRouteName(),
            Fixture\TestRoute::getTestRoutePath(),
            Fixture\TestRoute::getTestRouteRequestHandler()
        );

        $this->assertSame($expectedRoute, $createdRoute);
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
    public function testPostWithCustomRouteFactory() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $createdRoute = (new RouteCollector(new RouteCollection(), $routeFactory))->post(
            Fixture\TestRoute::getTestRouteName(),
            Fixture\TestRoute::getTestRoutePath(),
            Fixture\TestRoute::getTestRouteRequestHandler()
        );

        $this->assertSame($expectedRoute, $createdRoute);
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
    public function testPutWithCustomRouteFactory() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $createdRoute = (new RouteCollector(new RouteCollection(), $routeFactory))->put(
            Fixture\TestRoute::getTestRouteName(),
            Fixture\TestRoute::getTestRoutePath(),
            Fixture\TestRoute::getTestRouteRequestHandler()
        );

        $this->assertSame($expectedRoute, $createdRoute);
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
    public function testPatchWithCustomRouteFactory() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $createdRoute = (new RouteCollector(new RouteCollection(), $routeFactory))->patch(
            Fixture\TestRoute::getTestRouteName(),
            Fixture\TestRoute::getTestRoutePath(),
            Fixture\TestRoute::getTestRouteRequestHandler()
        );

        $this->assertSame($expectedRoute, $createdRoute);
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
    public function testDeleteWithCustomRouteFactory() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $createdRoute = (new RouteCollector(new RouteCollection(), $routeFactory))->delete(
            Fixture\TestRoute::getTestRouteName(),
            Fixture\TestRoute::getTestRoutePath(),
            Fixture\TestRoute::getTestRouteRequestHandler()
        );

        $this->assertSame($expectedRoute, $createdRoute);
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
    public function testPurgeWithCustomRouteFactory() : void
    {
        $expectedRoute = new Fixture\TestRoute();

        $routeFactory = $this->createMock(RouteFactoryInterface::class);
        $routeFactory->method('createRoute')->willReturn($expectedRoute);

        $createdRoute = (new RouteCollector(new RouteCollection(), $routeFactory))->purge(
            Fixture\TestRoute::getTestRouteName(),
            Fixture\TestRoute::getTestRoutePath(),
            Fixture\TestRoute::getTestRouteRequestHandler()
        );

        $this->assertSame($expectedRoute, $createdRoute);
    }

    /**
     * @return void
     */
    public function testGroup() : void
    {
        $collector = new RouteCollector();
        $collector->get('home', '/', new Fixture\BlankRequestHandler());

        $this->assertInstanceOf(
            RouteCollectionGroupActionInterface::class,
            $collector->group(function ($group) {
                $group->get('api.ping', '/ping', new Fixture\BlankRequestHandler());

                $this->assertInstanceOf(
                    RouteCollectionGroupActionInterface::class,
                    $group->group(function ($group) {

                        $this->assertInstanceOf(
                            RouteCollectionGroupActionInterface::class,
                            $group->group(function ($group) {
                                $group->post('api.section.create', '/create', new Fixture\BlankRequestHandler());
                                $group->patch('api.section.update', '/update/{id}', new Fixture\BlankRequestHandler());
                            })->addPrefix('/section')
                        );

                        $this->assertInstanceOf(
                            RouteCollectionGroupActionInterface::class,
                            $group->group(function ($group) {
                                $group->post('api.product.create', '/create', new Fixture\BlankRequestHandler());
                                $group->patch('api.product.update', '/update/{id}', new Fixture\BlankRequestHandler());
                            })->addPrefix('/product')
                        );
                    })->addPrefix('/v1')
                );
            })->addPrefix('/api')
        );

        $collector->get('about-us', '/about-us', new Fixture\BlankRequestHandler());

        $this->assertContains([
            'name' => 'home',
            'path' => '/',
            'methods' => [Router::METHOD_GET],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($collector->getCollection()));

        $this->assertContains([
            'name' => 'api.ping',
            'path' => '/api/ping',
            'methods' => [Router::METHOD_GET],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($collector->getCollection()));

        $this->assertContains([
            'name' => 'api.section.create',
            'path' => '/api/v1/section/create',
            'methods' => [Router::METHOD_POST],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($collector->getCollection()));

        $this->assertContains([
            'name' => 'api.section.update',
            'path' => '/api/v1/section/update/{id}',
            'methods' => [Router::METHOD_PATCH],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($collector->getCollection()));

        $this->assertContains([
            'name' => 'api.product.create',
            'path' => '/api/v1/product/create',
            'methods' => [Router::METHOD_POST],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($collector->getCollection()));

        $this->assertContains([
            'name' => 'api.product.update',
            'path' => '/api/v1/product/update/{id}',
            'methods' => [Router::METHOD_PATCH],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($collector->getCollection()));

        $this->assertContains([
            'name' => 'about-us',
            'path' => '/about-us',
            'methods' => [Router::METHOD_GET],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler',
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($collector->getCollection()));
    }
}
