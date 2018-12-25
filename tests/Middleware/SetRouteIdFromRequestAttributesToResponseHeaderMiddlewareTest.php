<?php

namespace Sunrise\Http\Router\Tests\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SetRouteIdFromRequestAttributesToResponseHeaderMiddlewareTest implements MiddlewareInterface
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
	{
		$routeId = $request->getAttribute('@route');

		return $handler->handle($request)->withHeader('x-route-id', $routeId);
	}
}
