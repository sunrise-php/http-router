<?php

namespace Sunrise\Http\Router\Tests;

use Fig\Http\Message\RequestMethodInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\RouterInterface;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

// fake middlewares
use Sunrise\Http\Router\Tests\Middleware\FooMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\BarMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\BazMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\QuxMiddlewareTest;

class RouterTest extends TestCase
{
	public function testConstructor()
	{
		$routes = new RouteCollection();
		$router = new Router($routes);

		$this->assertInstanceOf(RouterInterface::class, $router);
		$this->assertInstanceOf(RequestHandlerInterface::class, $router);
	}

	public function testGetMiddlewareStack()
	{
		$routes = new RouteCollection();
		$router = new Router($routes);

		$this->assertEquals([], $router->getMiddlewareStack());
	}

	public function testAddMiddleware()
	{
		$foo = new FooMiddlewareTest();

		$routes = new RouteCollection();
		$router = new Router($routes);

		$this->assertInstanceOf(RouterInterface::class, $router->addMiddleware($foo));

		$this->assertEquals([$foo], $router->getMiddlewareStack());
	}

	public function testAddSeveralMiddlewares()
	{
		$foo = new FooMiddlewareTest();
		$bar = new BarMiddlewareTest();

		$routes = new RouteCollection();
		$router = new Router($routes);

		$router->addMiddleware($foo);
		$router->addMiddleware($bar);

		$this->assertEquals([
			$foo,
			$bar,
		], $router->getMiddlewareStack());
	}

	public function testMatch()
	{
		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$routes = new RouteCollection();
		$routes->get('foo', '/foo');
		$routes->get('bar', '/bar');
		$routes->get('baz', '/baz');
		$router = new Router($routes);

		$expected = $routes->get('home', '/');
		$actual = $router->match($request);

		$this->assertEquals($expected->getId(), $actual->getId());
	}

	public function testMatchSimple404()
	{
		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$routes = new RouteCollection();
		$routes->get('foo', '/foo');
		$routes->get('bar', '/bar');
		$routes->get('baz', '/baz');
		$router = new Router($routes);

		$this->expectException(RouteNotFoundException::class);

		$router->match($request);
	}

	public function testMatchSimple405()
	{
		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/foo');

		$routes = new RouteCollection();
		$routes->post('foo', '/foo');
		$routes->patch('foo', '/foo');
		$routes->delete('foo', '/foo');
		$router = new Router($routes);

		$this->expectException(MethodNotAllowedException::class);

		try
		{
			$router->match($request);
		}
		catch (MethodNotAllowedException $e)
		{
			$this->assertEquals(['POST', 'PATCH', 'DELETE'], $e->getAllowedMethods());

			throw $e;
		}
	}

	public function testMatchAdvanced()
	{
		$routes = new RouteCollection();

		$static    = $routes->get('static', '/foo');
		$dynamic   = $routes->get('dynamic', '/bar/{p}');
		$digit     = $routes->get('digit', '/baz/{p}')->addPattern('p', '\d+');
		$word      = $routes->get('word', '/qux/{p}')->addPattern('p', '\w+');
		$asterisk  = $routes->get('asterisk', '{p}')->addPattern('p', '.*?');
		$ephemeral = $routes->get('ephemeral', '/quux(/{p1}(/{p2}))');

		$router = new Router($routes);

		$route = $router->match((new ServerRequestFactory)->createServerRequest('GET', '/quux'));
		$this->assertEquals($ephemeral->getId(), $route->getId());
		$this->assertArraySubset(['p1' => '', 'p2' => ''], $route->getAttributes());

		$route = $router->match((new ServerRequestFactory)->createServerRequest('GET', ''));
		$this->assertEquals($asterisk->getId(), $route->getId());
		$this->assertArraySubset(['p' => ''], $route->getAttributes());

		$route = $router->match((new ServerRequestFactory)->createServerRequest('GET', '/123/qwerty'));
		$this->assertEquals($asterisk->getId(), $route->getId());
		$this->assertArraySubset(['p' => '/123/qwerty'], $route->getAttributes());

		$route = $router->match((new ServerRequestFactory)->createServerRequest('GET', '/qux/qwerty'));
		$this->assertEquals($word->getId(), $route->getId());
		$this->assertArraySubset(['p' => 'qwerty'], $route->getAttributes());

		$route = $router->match((new ServerRequestFactory)->createServerRequest('GET', '/baz/123'));
		$this->assertEquals($digit->getId(), $route->getId());
		$this->assertArraySubset(['p' => '123'], $route->getAttributes());

		$route = $router->match((new ServerRequestFactory)->createServerRequest('GET', '/bar/qwerty'));
		$this->assertEquals($dynamic->getId(), $route->getId());
		$this->assertArraySubset(['p' => 'qwerty'], $route->getAttributes());

		$route = $router->match((new ServerRequestFactory)->createServerRequest('GET', '/bar/123'));
		$this->assertEquals($dynamic->getId(), $route->getId());
		$this->assertArraySubset(['p' => '123'], $route->getAttributes());

		$route = $router->match((new ServerRequestFactory)->createServerRequest('GET', '/foo'));
		$this->assertEquals($static->getId(), $route->getId());
	}

	public function testHandle()
	{
		$routes = new RouteCollection();

		$routes->get('home', '/')
		->addMiddleware(new BazMiddlewareTest())
		->addMiddleware(new QuxMiddlewareTest());

		$router = new Router($routes);
		$router->addMiddleware(new FooMiddlewareTest());
		$router->addMiddleware(new BarMiddlewareTest());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $router->handle($request);

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

		$methodNotAllowedException = new MethodNotAllowedException($request, ['HEAD', 'GET']);
		$this->assertInstanceOf(HttpExceptionInterface::class, $methodNotAllowedException);
		$this->assertInstanceOf(\RuntimeException::class, $methodNotAllowedException);
		$this->assertEquals(['HEAD', 'GET'], $methodNotAllowedException->getAllowedMethods());
	}
}
