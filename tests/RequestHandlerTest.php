<?php

namespace Sunrise\Http\ServerRequest\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\RequestHandler;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

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

		$this->assertInstanceOf(RequestHandlerInterface::class, $handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return $handler->handle($request);
			}
		}));
	}

	public function testHandleQueue()
	{
		$handler = new RequestHandler();

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return $handler->handle($request)->withAddedHeader('x-queue', 'foo');
			}
		});

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return $handler->handle($request)->withAddedHeader('x-queue', 'bar');
			}
		});

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return $handler->handle($request)->withAddedHeader('x-queue', 'baz');
			}
		});

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals([
			'x-queue' => ['baz', 'bar', 'foo'],
		], $response->getHeaders());
	}

	public function testHandleBreakQueueAtBeginning()
	{
		$handler = new RequestHandler();

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return (new ResponseFactory)->createResponse()->withAddedHeader('x-queue', 'foo');
			}
		});

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return $handler->handle($request)->withAddedHeader('x-queue', 'bar');
			}
		});

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return $handler->handle($request)->withAddedHeader('x-queue', 'baz');
			}
		});

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals([
			'x-queue' => ['foo'],
		], $response->getHeaders());
	}

	public function testHandleBreakQueueAtMiddle()
	{
		$handler = new RequestHandler();

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return $handler->handle($request)->withAddedHeader('x-queue', 'foo');
			}
		});

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return (new ResponseFactory)->createResponse()->withAddedHeader('x-queue', 'bar');
			}
		});

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return $handler->handle($request)->withAddedHeader('x-queue', 'baz');
			}
		});

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals([
			'x-queue' => ['bar', 'foo'],
		], $response->getHeaders());
	}

	public function testHandleBreakQueueAtEnd()
	{
		$handler = new RequestHandler();

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return $handler->handle($request)->withAddedHeader('x-queue', 'foo');
			}
		});

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return $handler->handle($request)->withAddedHeader('x-queue', 'bar');
			}
		});

		$handler->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
				return (new ResponseFactory)->createResponse()->withAddedHeader('x-queue', 'baz');
			}
		});

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $handler->handle($request);

		$this->assertEquals([
			'x-queue' => ['baz', 'bar', 'foo'],
		], $response->getHeaders());
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
