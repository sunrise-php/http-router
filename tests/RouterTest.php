<?php

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\RouterInterface;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

// fake middlewares
use Sunrise\Http\Router\Tests\Middleware\FooMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\BarMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\BazMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\QuxMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\SetRequestAttributesWithoutRouteIdToResponseHeaderMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\SetRouteIdFromRequestAttributesToResponseHeaderMiddlewareTest;

class RouterTest extends TestCase
{
	public function testConstructor()
	{
		$router = new Router();

		$this->assertInstanceOf(RouterInterface::class, $router);
		$this->assertInstanceOf(RequestHandlerInterface::class, $router);
	}

	public function testGetRoutes()
	{
		$router = new Router();

		$this->assertEquals([], $router->getRoutes());
	}

	public function testGetMiddlewareStack()
	{
		$router = new Router();

		$this->assertEquals([], $router->getMiddlewareStack());
	}

	public function testAddRoute()
	{
		$foo = new Route('foo', '/foo', []);
		$router = new Router();

		$this->assertInstanceOf(RouterInterface::class, $router->addRoute($foo));
		$this->assertEquals([$foo], $router->getRoutes());
	}

	public function testAddSeveralRoutes()
	{
		$foo = new Route('foo', '/foo', []);
		$bar = new Route('bar', '/bar', []);

		$router = new Router();
		$router->addRoute($foo);
		$router->addRoute($bar);

		$this->assertEquals([
			$foo,
			$bar,
		], $router->getRoutes());
	}

	public function testAddRoutesFromRouteCollection()
	{
		$router = new Router();
		$routes = new RouteCollection();

		$foo = $routes->get('foo', '/foo');

		$this->assertInstanceOf(RouterInterface::class, $router->addRoutes($routes));
		$this->assertEquals([$foo], $router->getRoutes());
	}

	public function testAddRoutesFromSeveralRouteCollections()
	{
		$router = new Router();

		$routes = new RouteCollection();
		$foo = $routes->get('foo', '/foo');
		$bar = $routes->get('bar', '/bar');
		$router->addRoutes($routes);

		$routes = new RouteCollection();
		$baz = $routes->get('baz', '/baz');
		$qux = $routes->get('qux', '/qux');
		$router->addRoutes($routes);

		$this->assertEquals([
			$foo,
			$bar,
			$baz,
			$qux,
		], $router->getRoutes());
	}

	public function testAddMiddleware()
	{
		$foo = new FooMiddlewareTest();
		$router = new Router();

		$this->assertInstanceOf(RouterInterface::class, $router->addMiddleware($foo));
		$this->assertEquals([$foo], $router->getMiddlewareStack());
	}

	public function testAddSeveralMiddlewares()
	{
		$foo = new FooMiddlewareTest();
		$bar = new BarMiddlewareTest();

		$router = new Router();
		$router->addMiddleware($foo);
		$router->addMiddleware($bar);

		$this->assertEquals([
			$foo,
			$bar,
		], $router->getMiddlewareStack());
	}

	public function testMatchWithSeveralRoutes()
	{
		$routes = new RouteCollection();
		$routes->get('foo', '/foo');
		$routes->get('bar', '/bar');
		$routes->get('baz', '/baz');

		$expected = $routes->get('qux', '/qux');
		$actual = $this->discoverRoute($routes, 'GET', '/qux');

		$this->assertEquals($expected, $actual);
	}

	public function testMatchWithSeveralHttpMethods()
	{
		$routes = new RouteCollection();

		$foo = $routes->head('foo', '/foo');
		$bar = $routes->get('bar', '/bar');
		$baz = $routes->post('baz', '/baz');

		$route = $this->discoverRoute($routes, 'HEAD', '/foo');
		$this->assertEquals($foo, $route);

		$route = $this->discoverRoute($routes, 'GET', '/foo');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'POST', '/foo');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'GET', '/bar');
		$this->assertEquals($bar, $route);

		$route = $this->discoverRoute($routes, 'HEAD', '/bar');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'POST', '/bar');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'POST', '/baz');
		$this->assertEquals($baz, $route);

		$route = $this->discoverRoute($routes, 'HEAD', '/baz');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'GET', '/baz');
		$this->assertNull($route);
	}

	public function testMatchAttributes()
	{
		$routes = new RouteCollection();
		$routes->get('test', '/{foo}/{bar}/{baz}');

		$route = $this->discoverRoute($routes, 'GET', '/first/second/third');
		$this->assertEquals($route->getAttributes(), [
			'foo' => 'first',
			'bar' => 'second',
			'baz' => 'third',
		]);

		$route = $this->discoverRoute($routes, 'GET', '/');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'GET', '/first');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'GET', '/first/second');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'GET', '/first/second/third/fourth');
		$this->assertNull($route);
	}

	public function testMatchOptionalAttributes()
	{
		$routes = new RouteCollection();
		$routes->get('test', '/{foo}(/{bar}/{baz})/{qux}');

		$route = $this->discoverRoute($routes, 'GET', '/first/second');
		$this->assertEquals($route->getAttributes(), [
			'foo' => 'first',
			'qux' => 'second',
		]);

		$route = $this->discoverRoute($routes, 'GET', '/first/second/third/fourth');
		$this->assertEquals($route->getAttributes(), [
			'foo' => 'first',
			'bar' => 'second',
			'baz' => 'third',
			'qux' => 'fourth',
		]);

		$route = $this->discoverRoute($routes, 'GET', '/first');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'GET', '/first/second/third/fourth/fifth');
		$this->assertNull($route);
	}

	public function testMatchNestedOptionalAttributes()
	{
		$routes = new RouteCollection();
		$routes->get('test', '/{foo}(/{bar}(/{baz})/{qux})/{quux}');

		$route = $this->discoverRoute($routes, 'GET', '/first/second');
		$this->assertEquals($route->getAttributes(), [
			'foo' => 'first',
			'quux' => 'second',
		]);

		$route = $this->discoverRoute($routes, 'GET', '/first/second/third/fourth');
		$this->assertEquals($route->getAttributes(), [
			'foo' => 'first',
			'bar' => 'second',
			'qux' => 'third',
			'quux' => 'fourth',
		]);

		$route = $this->discoverRoute($routes, 'GET', '/first/second/third/fourth/fifth');
		$this->assertEquals($route->getAttributes(), [
			'foo' => 'first',
			'bar' => 'second',
			'baz' => 'third',
			'qux' => 'fourth',
			'quux' => 'fifth',
		]);

		$route = $this->discoverRoute($routes, 'GET', '/first');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'GET', '/first/second/third');
		$this->assertNull($route);
	}

	public function testMatchPatterns()
	{
		$routes = new RouteCollection();

		$routes->get('test', '/{foo}/{bar}(/{baz})')
			->addPattern('foo', '[0-9]+')
			->addPattern('bar', '[a-z]+')
			->addPattern('baz', '.*?');

		$route = $this->discoverRoute($routes, 'GET', '/1990/Surgut/Tyumen');
		$this->assertEquals($route->getAttributes(), [
			'foo' => '1990',
			'bar' => 'Surgut',
			'baz' => 'Tyumen',
		]);

		$route = $this->discoverRoute($routes, 'GET', '/1990/Surgut/Tyumen/Moscow');
		$this->assertEquals($route->getAttributes(), [
			'foo' => '1990',
			'bar' => 'Surgut',
			'baz' => 'Tyumen/Moscow',
		]);

		$route = $this->discoverRoute($routes, 'GET', '/Oops/Surgut/Tyumen/Moscow');
		$this->assertNull($route);

		$route = $this->discoverRoute($routes, 'GET', '/1990/2018/Moscow');
		$this->assertNull($route);
	}

	public function testMatchRouteNotFoundException()
	{
		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/oops');

		$routes = new RouteCollection();
		$routes->get('test', '/');

		$router = new Router();
		$router->addRoutes($routes);

		$this->expectException(RouteNotFoundException::class);
		$router->match($request);
	}

	public function testMatchMethodNotAllowedException()
	{
		$request = (new ServerRequestFactory)
		->createServerRequest('POST', '/');

		$routes = new RouteCollection();
		$routes->route('test', '/', ['HEAD', 'GET', 'OPTIONS']);

		$router = new Router();
		$router->addRoutes($routes);

		$this->expectException(MethodNotAllowedException::class);

		try {
			$router->match($request);
		} catch (MethodNotAllowedException $e) {
			$this->assertEquals(['HEAD', 'GET', 'OPTIONS'], $e->getAllowedMethods());
			throw $e;
		}
	}

	public function testHandle()
	{
		$routes = new RouteCollection();

		$routes->get('test', '/{foo}/{bar}/{baz}')
		->addMiddleware(new SetRequestAttributesWithoutRouteIdToResponseHeaderMiddlewareTest())
		->addMiddleware(new SetRouteIdFromRequestAttributesToResponseHeaderMiddlewareTest())
		->addMiddleware(new BazMiddlewareTest())
		->addMiddleware(new QuxMiddlewareTest());

		$router = new Router();
		$router->addRoutes($routes);
		$router->addMiddleware(new FooMiddlewareTest());
		$router->addMiddleware(new BarMiddlewareTest());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/first/second/third');

		$response = $router->handle($request);

		$this->assertEquals(['test'], $response->getHeader('x-route-id'));

		$this->assertEquals(['first, second, third'], $response->getHeader('x-request-attributes'));

		$this->assertEquals([
			'qux',
			'baz',
			'bar',
			'foo',
		], $response->getHeader('x-middleware'));
	}

	public function testExceptions()
	{
		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$routeNotFoundException = new RouteNotFoundException($request);
		$this->assertInstanceOf(HttpExceptionInterface::class, $routeNotFoundException);
		$this->assertInstanceOf(\RuntimeException::class, $routeNotFoundException);
		$this->assertEquals($request, $routeNotFoundException->getRequest());

		$methodNotAllowedException = new MethodNotAllowedException($request, ['HEAD', 'GET']);
		$this->assertInstanceOf(HttpExceptionInterface::class, $methodNotAllowedException);
		$this->assertInstanceOf(\RuntimeException::class, $methodNotAllowedException);
		$this->assertEquals($request, $methodNotAllowedException->getRequest());
		$this->assertEquals(['HEAD', 'GET'], $methodNotAllowedException->getAllowedMethods());
	}

	private function discoverRoute(RouteCollectionInterface $routes, string $method, string $uri) : ?RouteInterface
	{
		$router = new Router();
		$router->addRoutes($routes);

		$request = (new ServerRequestFactory)
		->createServerRequest($method, $uri);

		try {
			return $router->match($request);
		} catch (HttpExceptionInterface $error) {
			return null;
		}
	}
}
