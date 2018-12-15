<?php

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

class RouteTest extends TestCase
{
	public function testConstructor()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertInstanceOf(MiddlewareInterface::class, $route);
	}

	public function testGetId()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertEquals('home', $route->getId());
	}

	public function testGetPath()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertEquals('/', $route->getPath());
	}

	public function testGetAction()
	{
		$action = $this->getRouteAction();

		$route = new Route('home', '/', $action);

		$this->assertEquals($action, $route->getAction());
	}

	public function testGetMethods()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertEquals([], $route->getMethods());
	}

	public function testGetPatterns()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertEquals([], $route->getPatterns());
	}

	public function testGetMiddlewareStack()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertEquals([], $route->getMiddlewareStack());
	}

	public function testGetAttributes()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertEquals([], $route->getAttributes());
	}

	public function testAddPrefix()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertInstanceOf(RouteInterface::class, $route->prefix('/foo'));
		$this->assertEquals('/foo/', $route->getPath());
	}

	public function testAddMethod()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertInstanceOf(RouteInterface::class, $route->method('GET'));
		$this->assertEquals(['GET'], $route->getMethods());
	}

	public function testAddSeveralMethods()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$route->method('GET');
		$route->method('POST');

		$this->assertEquals(['GET', 'POST'], $route->getMethods());
	}

	public function testAddLowercasedMethod()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$route->method('get');

		$this->assertEquals(['GET'], $route->getMethods());
	}

	public function testAddPattern()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertInstanceOf(RouteInterface::class, $route->pattern('id', '\d+'));
		$this->assertEquals(['id' => '\d+'], $route->getPatterns());
	}

	public function testAddSeveralPatterns()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$route->pattern('id', '\d+');
		$route->pattern('word', '\w+');

		$this->assertEquals([
			'id' => '\d+',
			'word' => '\w+',
		], $route->getPatterns());
	}

	public function testAddMiddleware()
	{
		$foo = $this->getRouteMiddlewareFoo();

		$route = new Route('home', '/', $this->getRouteAction());

		$this->assertInstanceOf(RouteInterface::class, $route->middleware($foo));
		$this->assertEquals([$foo], $route->getMiddlewareStack());
	}

	public function testAddSeveralMiddleware()
	{
		$foo = $this->getRouteMiddlewareFoo();
		$bar = $this->getRouteMiddlewareBar();

		$route = new Route('home', '/', $this->getRouteAction());

		$route->middleware($foo);
		$route->middleware($bar);

		$this->assertEquals([$foo, $bar], $route->getMiddlewareStack());
	}

	public function testSetAttributes()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$clone = $route->withAttributes(['id' => '1']);
		$this->assertInstanceOf(RouteInterface::class, $clone);
		$this->assertEquals(['id' => '1'], $clone->getAttributes());

		$this->assertEquals([], $route->getAttributes());
	}

	public function testProcess()
	{
		$route = new Route('home', '/', $this->getRouteAction());

		$handler = new RequestHandler();
		$handler->add($route);

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals(['true'], $response->getHeader('x-test'));
	}

	private function getRouteAction() : callable
	{
		return function(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
		{
			return $response->withHeader('x-test', 'true');
		};
	}

	private function getRouteMiddlewareFoo() : MiddlewareInterface
	{
		return new class implements MiddlewareInterface
		{
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
			{
				return $handler->handle($request);
			}
		};
	}

	private function getRouteMiddlewareBar() : MiddlewareInterface
	{
		return new class implements MiddlewareInterface
		{
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
			{
				return $handler->handle($request);
			}
		};
	}
}
