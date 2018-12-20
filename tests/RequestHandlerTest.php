<?php

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

// fake middlewares
use Sunrise\Http\Router\Tests\Middleware\FooMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\BarMiddlewareTest;
use Sunrise\Http\Router\Tests\Middleware\BazMiddlewareTest;

class RequestHandlerTest extends TestCase
{
	public function testConstructor()
	{
		$handler = new RequestHandler();

		$this->assertInstanceOf(RequestHandlerInterface::class, $handler);
	}

	public function testAdd()
	{
		$handler = new RequestHandler();

		$this->assertInstanceOf(RequestHandlerInterface::class, $handler->add(new FooMiddlewareTest()));
	}

	public function testQueue()
	{
		$handler = new RequestHandler();

		$handler->add(new FooMiddlewareTest());
		$handler->add(new BarMiddlewareTest());
		$handler->add(new BazMiddlewareTest());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals([
			'baz',
			'bar',
			'foo',
		], $response->getHeader('x-middleware'));
	}

	public function testBreakingQueueAtBeginning()
	{
		$handler = new RequestHandler();

		$handler->add(new FooMiddlewareTest(true));
		$handler->add(new BarMiddlewareTest());
		$handler->add(new BazMiddlewareTest());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals([
			'foo',
		], $response->getHeader('x-middleware'));
	}

	public function testBreakingQueueInMiddle()
	{
		$handler = new RequestHandler();

		$handler->add(new FooMiddlewareTest());
		$handler->add(new BarMiddlewareTest(true));
		$handler->add(new BazMiddlewareTest());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals([
			'bar',
			'foo',
		], $response->getHeader('x-middleware'));
	}

	public function testBreakingQueueAtEnd()
	{
		$handler = new RequestHandler();

		$handler->add(new FooMiddlewareTest());
		$handler->add(new BarMiddlewareTest());
		$handler->add(new BazMiddlewareTest(true));

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals([
			'baz',
			'bar',
			'foo',
		], $response->getHeader('x-middleware'));
	}

	public function testEmptyStack()
	{
		$handler = new RequestHandler();

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertInstanceOf(ResponseInterface::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
	}
}
