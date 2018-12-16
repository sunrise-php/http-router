<?php

namespace Sunrise\Http\Router\Tests;

use Fig\Http\Message\RequestMethodInterface;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\RouteCollectionInterface;

class RouteCollectionTest extends TestCase
{
	use HelpersInjectTest;

	public function testConstructor()
	{
		$collection = new RouteCollection();

		$this->assertInstanceOf(RouteCollectionInterface::class, $collection);
	}

	public function testGetRoute()
	{
		$collection = new RouteCollection();

		// default value
		$this->assertNull($collection->getRoute('undefined.route.id'));
	}

	public function testGetRoutes()
	{
		$collection = new RouteCollection();

		// default value
		$this->assertEquals([], $collection->getRoutes());
	}

	public function testGetMiddlewareStack()
	{
		$collection = new RouteCollection();

		// default value
		$this->assertEquals([], $collection->getMiddlewareStack());
	}

	public function testAddRoute()
	{
		$route = $this->getRouteFoo();
		$collection = new RouteCollection();

		$this->assertInstanceOf(RouteCollectionInterface::class, $collection->addRoute($route));
		$this->assertEquals([$route->getId() => $route], $collection->getRoutes());
		$this->assertEquals($route, $collection->getRoute($route->getId()));
	}

	public function testAddSeveralRoutes()
	{
		$foo = $this->getRouteFoo();
		$bar = $this->getRouteBar();
		$baz = $this->getRouteBaz();

		$collection = new RouteCollection();

		$collection->addRoute($foo);
		$collection->addRoute($bar);
		$collection->addRoute($baz);

		$this->assertEquals([
			$foo->getId() => $foo,
			$bar->getId() => $bar,
			$baz->getId() => $baz,
		], $collection->getRoutes());

		$this->assertEquals($foo, $collection->getRoute($foo->getId()));
		$this->assertEquals($bar, $collection->getRoute($bar->getId()));
		$this->assertEquals($baz, $collection->getRoute($baz->getId()));
	}

	public function testAddMiddleware()
	{
		$middleware = $this->getMiddlewareFoo();
		$collection = new RouteCollection();

		$this->assertInstanceOf(RouteCollectionInterface::class, $collection->middleware($middleware));
		$this->assertEquals([$middleware], $collection->getMiddlewareStack());
	}

	public function testAddSeveralMiddlewares()
	{
		$foo = $this->getMiddlewareFoo();
		$bar = $this->getMiddlewareBar();
		$baz = $this->getMiddlewareBaz();

		$collection = new RouteCollection();

		$collection->middleware($foo);
		$collection->middleware($bar);
		$collection->middleware($baz);

		$this->assertEquals([
			$foo,
			$bar,
			$baz,
		], $collection->getMiddlewareStack());
	}

	public function testCreateRoute()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [];

		$collection = new RouteCollection();
		$route = $collection->add($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteWithHttpMethod()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_GET,
		];

		$collection = new RouteCollection();
		$route = $collection->add($routeId, $routePath, $routeAction, $routeMethods);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteWithSeveralHttpMethods()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_GET,
			RequestMethodInterface::METHOD_POST,
		];

		$collection = new RouteCollection();
		$route = $collection->add($routeId, $routePath, $routeAction, $routeMethods);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodHead()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_HEAD,
		];

		$collection = new RouteCollection();
		$route = $collection->head($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodGet()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_GET,
		];

		$collection = new RouteCollection();
		$route = $collection->get($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodPost()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_POST,
		];

		$collection = new RouteCollection();
		$route = $collection->post($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodPut()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_PUT,
		];

		$collection = new RouteCollection();
		$route = $collection->put($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodPatch()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_PATCH,
		];

		$collection = new RouteCollection();
		$route = $collection->patch($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodDelete()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_DELETE,
		];

		$collection = new RouteCollection();
		$route = $collection->delete($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodPurge()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_PURGE,
		];

		$collection = new RouteCollection();
		$route = $collection->purge($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodOptions()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_OPTIONS,
		];

		$collection = new RouteCollection();
		$route = $collection->options($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodTrace()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_TRACE,
		];

		$collection = new RouteCollection();
		$route = $collection->trace($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodConnect()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_CONNECT,
		];

		$collection = new RouteCollection();
		$route = $collection->connect($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodSafe()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_HEAD,
			RequestMethodInterface::METHOD_GET,
		];

		$collection = new RouteCollection();
		$route = $collection->safe($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testCreateRouteForHttpMethodAny()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeAction = $this->getRouteActionFoo();
		$routeMethods = [
			RequestMethodInterface::METHOD_HEAD,
			RequestMethodInterface::METHOD_GET,
			RequestMethodInterface::METHOD_POST,
			RequestMethodInterface::METHOD_PUT,
			RequestMethodInterface::METHOD_PATCH,
			RequestMethodInterface::METHOD_DELETE,
			RequestMethodInterface::METHOD_PURGE,
			RequestMethodInterface::METHOD_OPTIONS,
			RequestMethodInterface::METHOD_TRACE,
			RequestMethodInterface::METHOD_CONNECT,
		];

		$collection = new RouteCollection();
		$route = $collection->any($routeId, $routePath, $routeAction);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeAction, $route->getAction());
		$this->assertEquals($routeMethods, $route->getMethods());
	}

	public function testGroupPrefix()
	{
		$foo = $this->getRouteFoo();
		$bar = $this->getRouteBar();
		$baz = $this->getRouteBaz();
		$qux = $this->getRouteQux();

		$collection = new RouteCollection();

		$collection->group('/a', function(RouteCollectionInterface $collection) use($bar, $baz, $qux)
		{
			$collection->addRoute(clone $bar);

			$collection->group('/b', function(RouteCollectionInterface $collection) use($baz)
			{
				$collection->addRoute(clone $baz);
			});

			$collection->addRoute(clone $qux);
		});

		$collection->addRoute(clone $foo);

		$this->assertEquals($foo->getPath(), $collection->getRoute($foo->getId())->getPath());
		$this->assertEquals($foo->getMiddlewareStack(), $collection->getRoute($foo->getId())->getMiddlewareStack());

		$this->assertEquals('/a' . $bar->getPath(), $collection->getRoute($bar->getId())->getPath());
		$this->assertEquals($bar->getMiddlewareStack(), $collection->getRoute($bar->getId())->getMiddlewareStack());

		$this->assertEquals('/a/b' . $baz->getPath(), $collection->getRoute($baz->getId())->getPath());
		$this->assertEquals($baz->getMiddlewareStack(), $collection->getRoute($baz->getId())->getMiddlewareStack());

		$this->assertEquals('/a' . $qux->getPath(), $collection->getRoute($qux->getId())->getPath());
		$this->assertEquals($qux->getMiddlewareStack(), $collection->getRoute($qux->getId())->getMiddlewareStack());
	}

	public function testGroupMiddleware()
	{
		$routeFoo = $this->getRouteFoo();
		$routeBar = $this->getRouteBar();
		$routeBaz = $this->getRouteBaz();
		$routeQux = $this->getRouteQux();

		$middlewareFoo = $this->getMiddlewareFoo();
		$middlewareBar = $this->getMiddlewareBar();
		$middlewareBaz = $this->getMiddlewareBaz();
		$middlewareQux = $this->getMiddlewareQux();

		$collection = new RouteCollection();

		$collection->group('/a', function(RouteCollectionInterface $collection) use(
			$routeFoo,
			$routeBar,
			$routeBaz,
			$routeQux,
			$middlewareFoo,
			$middlewareBar,
			$middlewareBaz,
			$middlewareQux
		)
		{
			$collection->addRoute(clone $routeFoo);
			$collection->addRoute(clone $routeBar);
			$collection->middleware($middlewareFoo);
			$collection->middleware($middlewareBar);

			$collection->group('/b', function(RouteCollectionInterface $collection) use(
				$routeBaz,
				$routeQux,
				$middlewareBaz,
				$middlewareQux
			)
			{
				$collection->addRoute(clone $routeBaz);
				$collection->addRoute(clone $routeQux);
				$collection->middleware($middlewareBaz);
				$collection->middleware($middlewareQux);
			});
		});

		$this->assertEquals([
			$middlewareFoo,
			$middlewareBar,
		], $collection->getRoute($routeFoo->getId())->getMiddlewareStack());

		$this->assertEquals([
			$middlewareFoo,
			$middlewareBar,
		], $collection->getRoute($routeBar->getId())->getMiddlewareStack());

		$this->assertEquals([
			$middlewareBaz,
			$middlewareQux,
			$middlewareFoo,
			$middlewareBar,
		], $collection->getRoute($routeBaz->getId())->getMiddlewareStack());

		$this->assertEquals([
			$middlewareBaz,
			$middlewareQux,
			$middlewareFoo,
			$middlewareBar,
		], $collection->getRoute($routeQux->getId())->getMiddlewareStack());
	}

	public function testCount()
	{
		$collection = new RouteCollection();
		$this->assertEquals(0, $collection->count());

		$collection->addRoute($this->getRouteFoo());
		$this->assertEquals(1, $collection->count());

		$collection->addRoute($this->getRouteBar());
		$collection->addRoute($this->getRouteBaz());
		$collection->addRoute($this->getRouteQux());
		$this->assertEquals(4, $collection->count());
	}
}
