<?php

namespace Sunrise\Http\Router\Tests\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;

class FooMiddlewareTest implements MiddlewareInterface
{
	private $breakable;

	public function __construct(bool $breakable = false)
	{
		$this->breakable = $breakable;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
	{
		$response = ! $this->breakable ? $handler->handle($request) : (new ResponseFactory)->createResponse();

		return $response
		->withAddedHeader('x-middleware', 'foo')
		->withAddedHeader('x-queue', 'middleware-foo');
	}
}
