<?php

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\RouteInterface;

// fake middlewares
use Sunrise\Http\Router\Tests\Middleware\FooMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\BarMiddlewareTest;

class RouteTest extends TestCase
{
	public function testConstructor()
	{
		$route = new Route('home', '/', []);

		$this->assertInstanceOf(RouteInterface::class, $route);
	}

	public function testGetId()
	{
		$foo = 'home';

		$route = new Route($foo, '/', []);

		$this->assertEquals($foo, $route->getId());
	}

	public function testGetPath()
	{
		$foo = '/';

		$route = new Route('home', $foo, []);

		$this->assertEquals($foo, $route->getPath());
	}

	public function testGetMethods()
	{
		$foo = ['HEAD', 'GET'];

		$route = new Route('home', '/', $foo);

		$this->assertEquals($foo, $route->getMethods());
	}

	public function testGetPatterns()
	{
		$foo = [];

		$route = new Route('home', '/', []);

		$this->assertEquals($foo, $route->getPatterns());
	}

	public function testGetAttributes()
	{
		$foo = [];

		$route = new Route('home', '/', []);

		$this->assertEquals($foo, $route->getAttributes());
	}

	public function testGetMiddlewareStack()
	{
		$foo = [];

		$route = new Route('home', '/', []);

		$this->assertEquals($foo, $route->getMiddlewareStack());
	}

	public function testAddPrefix()
	{
		$foo = '/foo';
		$bar = '/bar';

		$route = new Route('home', $foo, []);

		$this->assertInstanceOf(RouteInterface::class, $route->addPrefix($bar));

		$this->assertEquals($bar.$foo, $route->getPath());
	}

	public function testAddSeveralPrefixex()
	{
		$foo = '/foo';
		$bar = '/bar';
		$baz = '/baz';

		$route = new Route('home', $foo, []);

		$route->addPrefix($bar);
		$route->addPrefix($baz);

		$this->assertEquals($baz.$bar.$foo, $route->getPath());
	}

	public function testAddPattern()
	{
		$foo = ['id', '\d+'];

		$route = new Route('home', '/', []);

		$this->assertInstanceOf(RouteInterface::class, $route->addPattern($foo[0], $foo[1]));

		$this->assertEquals([$foo[0] => $foo[1]], $route->getPatterns());
	}

	public function testAddSeveralPatterns()
	{
		$foo = ['id', '\d+'];
		$bar = ['word', '\w+'];

		$route = new Route('home', '/', []);

		$route->addPattern($foo[0], $foo[1]);
		$route->addPattern($bar[0], $bar[1]);

		$this->assertEquals([
			$foo[0] => $foo[1],
			$bar[0] => $bar[1],
		], $route->getPatterns());
	}

	public function testAddMiddleware()
	{
		$foo = new FooMiddlewareTest();

		$route = new Route('home', '/', []);

		$this->assertInstanceOf(RouteInterface::class, $route->addMiddleware($foo));

		$this->assertEquals([$foo], $route->getMiddlewareStack());
	}

	public function testAddSeveralMiddlewares()
	{
		$foo = new FooMiddlewareTest();
		$bar = new BarMiddlewareTest();

		$route = new Route('home', '/', []);

		$route->addMiddleware($foo);
		$route->addMiddleware($bar);

		$this->assertEquals([
			$foo,
			$bar,
		], $route->getMiddlewareStack());
	}

	public function testSetAttributes()
	{
		$foo = ['id' => '1'];

		$route = new Route('home', '/', []);
		$clone = $route->withAttributes($foo);

		$this->assertInstanceOf(RouteInterface::class, $clone);
		$this->assertNotEquals($clone, $route);

		$this->assertEquals($foo, $clone->getAttributes());
		$this->assertEquals([], $route->getAttributes());
	}

	public function testSetAttributesPreservingPreviousValues()
	{
		$foo = ['foo' => 'bar'];
		$bar = ['bar' => 'baz'];
		$baz = ['baz' => 'qux'];

		$route = new Route('home', '/', []);
		$clone1 = $route->withAttributes($foo);
		$clone2 = $clone1->withAttributes($bar);
		$clone3 = $clone2->withAttributes($baz);

		$this->assertEquals([], $route->getAttributes());
		$this->assertEquals($foo, $clone1->getAttributes());
		$this->assertEquals($foo + $bar, $clone2->getAttributes());
		$this->assertEquals($foo + $bar + $baz, $clone3->getAttributes());
	}

	public function testLowercasedMethod()
	{
		$route = new Route('home', '/', ['foo', 'bar']);

		$this->assertEquals(['FOO', 'BAR'], $route->getMethods());
	}
}
