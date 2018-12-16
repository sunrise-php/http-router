<?php

namespace Sunrise\Http\Router\Tests;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\RouteInterface;

trait HelpersInjectTest
{
	public function routeActionFoo(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
	{
		return $response
		->withAddedHeader('x-route', 'foo')
		->withAddedHeader('x-queue', 'route-foo');
	}

	public function routeActionBar(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
	{
		return $response
		->withAddedHeader('x-route', 'bar')
		->withAddedHeader('x-queue', 'route-bar');
	}

	public function routeActionBaz(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
	{
		return $response
		->withAddedHeader('x-route', 'baz')
		->withAddedHeader('x-queue', 'route-baz');
	}

	public function routeActionQux(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
	{
		return $response
		->withAddedHeader('x-route', 'qux')
		->withAddedHeader('x-queue', 'route-qux');
	}

	public function getRouteActionFoo() : callable
	{
		return [$this, 'routeActionFoo'];
	}

	public function getRouteActionBar() : callable
	{
		return [$this, 'routeActionBar'];
	}

	public function getRouteActionBaz() : callable
	{
		return [$this, 'routeActionBaz'];
	}

	public function getRouteActionQux() : callable
	{
		return [$this, 'routeActionQux'];
	}

	public function getRouteFoo() : RouteInterface
	{
		return new Route('foo', '/foo', $this->getRouteActionFoo());
	}

	public function getRouteBar() : RouteInterface
	{
		return new Route('bar', '/bar', $this->getRouteActionBar());
	}

	public function getRouteBaz() : RouteInterface
	{
		return new Route('baz', '/baz', $this->getRouteActionBaz());
	}

	public function getRouteQux() : RouteInterface
	{
		return new Route('qux', '/qux', $this->getRouteActionQux());
	}

	public function getMiddlewareFoo(bool $next = true) : MiddlewareInterface
	{
		return new class($next) implements MiddlewareInterface
		{
			private $next;

			public function __construct(bool $next)
			{
				$this->next = $next;
			}

			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
			{
				$response = $this->next ? $handler->handle($request) : (new ResponseFactory)->createResponse();

				return $response
				->withAddedHeader('x-middleware', 'foo')
				->withAddedHeader('x-queue', 'middleware-foo');
			}
		};
	}

	public function getMiddlewareBar(bool $next = true) : MiddlewareInterface
	{
		return new class($next) implements MiddlewareInterface
		{
			private $next;

			public function __construct(bool $next)
			{
				$this->next = $next;
			}

			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
			{
				$response = $this->next ? $handler->handle($request) : (new ResponseFactory)->createResponse();

				return $response
				->withAddedHeader('x-middleware', 'bar')
				->withAddedHeader('x-queue', 'middleware-bar');
			}
		};
	}

	public function getMiddlewareBaz(bool $next = true) : MiddlewareInterface
	{
		return new class($next) implements MiddlewareInterface
		{
			private $next;

			public function __construct(bool $next)
			{
				$this->next = $next;
			}

			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
			{
				$response = $this->next ? $handler->handle($request) : (new ResponseFactory)->createResponse();

				return $response
				->withAddedHeader('x-middleware', 'baz')
				->withAddedHeader('x-queue', 'middleware-baz');
			}
		};
	}

	public function getMiddlewareQux(bool $next = true) : MiddlewareInterface
	{
		return new class($next) implements MiddlewareInterface
		{
			private $next;

			public function __construct(bool $next)
			{
				$this->next = $next;
			}

			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
			{
				$response = $this->next ? $handler->handle($request) : (new ResponseFactory)->createResponse();

				return $response
				->withAddedHeader('x-middleware', 'qux')
				->withAddedHeader('x-queue', 'middleware-qux');
			}
		};
	}
}
