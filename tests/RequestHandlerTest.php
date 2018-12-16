<?php

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

class RequestHandlerTest extends TestCase
{
	use HelpersInjectTest;

	public function testConstructor()
	{
		$handler = new RequestHandler();

		$this->assertInstanceOf(RequestHandlerInterface::class, $handler);
	}

	public function testAdd()
	{
		$handler = new RequestHandler();

		$this->assertInstanceOf(RequestHandlerInterface::class, $handler->add($this->getMiddlewareFoo()));
	}

	public function testHandleQueue()
	{
		$handler = new RequestHandler();

		$handler->add($this->getMiddlewareFoo());
		$handler->add($this->getMiddlewareBar());
		$handler->add($this->getMiddlewareBaz());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals(['baz', 'bar', 'foo'], $response->getHeader('x-middleware'));
	}

	public function testHandleBreakQueueAtBeginning()
	{
		$handler = new RequestHandler();

		$handler->add($this->getMiddlewareFoo(false));
		$handler->add($this->getMiddlewareBar());
		$handler->add($this->getMiddlewareBaz());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals(['foo'], $response->getHeader('x-middleware'));
	}

	public function testHandleBreakQueueAtMiddle()
	{
		$handler = new RequestHandler();

		$handler->add($this->getMiddlewareFoo());
		$handler->add($this->getMiddlewareBar(false));
		$handler->add($this->getMiddlewareBaz());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals(['bar', 'foo'], $response->getHeader('x-middleware'));
	}

	public function testHandleBreakQueueAtEnd()
	{
		$handler = new RequestHandler();

		$handler->add($this->getMiddlewareFoo());
		$handler->add($this->getMiddlewareBar());
		$handler->add($this->getMiddlewareBaz(false));

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals(['baz', 'bar', 'foo'], $response->getHeader('x-middleware'));
	}

	public function testHandleWithoutStack()
	{
		$handler = new RequestHandler();

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertInstanceOf(ResponseInterface::class, $response);
	}
}
