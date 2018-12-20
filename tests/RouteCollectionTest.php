<?php

namespace Sunrise\Http\Router\Tests;

use Fig\Http\Message\RequestMethodInterface;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\RouteCollectionInterface;

class RouteCollectionTest extends TestCase
{
	public function testConstructor()
	{
		$collection = new RouteCollection();

		$this->assertInstanceOf(RouteCollectionInterface::class, $collection);
	}

	public function testGetRoutes()
	{
		$collection = new RouteCollection();

		$this->assertEquals([], $collection->getRoutes());
	}

	public function testAddRoute()
	{
		$foo = new Route('foo', '/foo', []);

		$collection = new RouteCollection();

		$this->assertInstanceOf(RouteCollectionInterface::class, $collection->addRoute($foo));

		$this->assertEquals([$foo], $collection->getRoutes());
	}

	public function testAddSeveralRoutes()
	{
		$foo = new Route('foo', '/foo', []);
		$bar = new Route('bar', '/bar', []);

		$collection = new RouteCollection();

		$collection->addRoute($foo);
		$collection->addRoute($bar);

		$this->assertEquals([
			$foo,
			$bar,
		], $collection->getRoutes());
	}

	public function testCreateRoute()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeMethods = ['HEAD', 'GET'];

		$collection = new RouteCollection();
		$route = $collection->route($routeId, $routePath, $routeMethods);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeMethods, $route->getMethods());
		$this->assertEquals([$route], $collection->getRoutes());
	}

	public function testCreateRouteForHttpMethodHead()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeMethods = [
			RequestMethodInterface::METHOD_HEAD,
		];

		$collection = new RouteCollection();
		$route = $collection->head($routeId, $routePath);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeMethods, $route->getMethods());
		$this->assertEquals([$route], $collection->getRoutes());
	}

	public function testCreateRouteForHttpMethodGet()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeMethods = [
			RequestMethodInterface::METHOD_GET,
		];

		$collection = new RouteCollection();
		$route = $collection->get($routeId, $routePath);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeMethods, $route->getMethods());
		$this->assertEquals([$route], $collection->getRoutes());
	}

	public function testCreateRouteForHttpMethodPost()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeMethods = [
			RequestMethodInterface::METHOD_POST,
		];

		$collection = new RouteCollection();
		$route = $collection->post($routeId, $routePath);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeMethods, $route->getMethods());
		$this->assertEquals([$route], $collection->getRoutes());
	}

	public function testCreateRouteForHttpMethodPut()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeMethods = [
			RequestMethodInterface::METHOD_PUT,
		];

		$collection = new RouteCollection();
		$route = $collection->put($routeId, $routePath);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeMethods, $route->getMethods());
		$this->assertEquals([$route], $collection->getRoutes());
	}

	public function testCreateRouteForHttpMethodPatch()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeMethods = [
			RequestMethodInterface::METHOD_PATCH,
		];

		$collection = new RouteCollection();
		$route = $collection->patch($routeId, $routePath);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeMethods, $route->getMethods());
		$this->assertEquals([$route], $collection->getRoutes());
	}

	public function testCreateRouteForHttpMethodDelete()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeMethods = [
			RequestMethodInterface::METHOD_DELETE,
		];

		$collection = new RouteCollection();
		$route = $collection->delete($routeId, $routePath);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeMethods, $route->getMethods());
		$this->assertEquals([$route], $collection->getRoutes());
	}

	public function testCreateRouteForHttpMethodPurge()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeMethods = [
			RequestMethodInterface::METHOD_PURGE,
		];

		$collection = new RouteCollection();
		$route = $collection->purge($routeId, $routePath);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeMethods, $route->getMethods());
		$this->assertEquals([$route], $collection->getRoutes());
	}

	public function testCreateRouteForHttpMethodSafe()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeMethods = [
			RequestMethodInterface::METHOD_HEAD,
			RequestMethodInterface::METHOD_GET,
		];

		$collection = new RouteCollection();
		$route = $collection->safe($routeId, $routePath);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeMethods, $route->getMethods());
		$this->assertEquals([$route], $collection->getRoutes());
	}

	public function testCreateRouteForHttpMethodAny()
	{
		$routeId = 'foo';
		$routePath = '/foo';
		$routeMethods = [
			RequestMethodInterface::METHOD_HEAD,
			RequestMethodInterface::METHOD_GET,
			RequestMethodInterface::METHOD_POST,
			RequestMethodInterface::METHOD_PUT,
			RequestMethodInterface::METHOD_PATCH,
			RequestMethodInterface::METHOD_DELETE,
			RequestMethodInterface::METHOD_PURGE,
		];

		$collection = new RouteCollection();
		$route = $collection->any($routeId, $routePath);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals($routeId, $route->getId());
		$this->assertEquals($routePath, $route->getPath());
		$this->assertEquals($routeMethods, $route->getMethods());
		$this->assertEquals([$route], $collection->getRoutes());
	}

	public function testGroup()
	{
		$foo = new Route('foo', '/foo', []);
		$bar = new Route('bar', '/foo', []);
		$baz = new Route('baz', '/foo', []);
		$qux = new Route('qux', '/foo', []);

		$collection = new RouteCollection();

		$collection->group('/bar', function(RouteCollectionInterface $collection) use($foo, $bar, $baz)
		{
			$collection->addRoute($bar);

			$collection->group('/baz', function(RouteCollectionInterface $collection) use($foo, $baz)
			{
				$collection->group('/qux', function(RouteCollectionInterface $collection) use($foo)
				{
					$collection->addRoute($foo);
				});

				$collection->addRoute($baz);
			});
		});

		$collection->addRoute($qux);

		$this->assertEquals([
			$bar,
			$foo,
			$baz,
			$qux,
		], $collection->getRoutes());

		$this->assertEquals('/bar/baz/qux/foo', $foo->getPath());
		$this->assertEquals('/bar/foo', $bar->getPath());
		$this->assertEquals('/bar/baz/foo', $baz->getPath());
		$this->assertEquals('/foo', $qux->getPath());
	}
}
