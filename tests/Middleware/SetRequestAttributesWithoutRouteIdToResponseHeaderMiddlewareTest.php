<?php

namespace Sunrise\Http\Router\Tests\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SetRequestAttributesWithoutRouteIdToResponseHeaderMiddlewareTest implements MiddlewareInterface
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
	{
		$attributes = $request->getAttributes();

		unset($attributes['@route']);

		return $handler->handle($request)->withHeader('x-request-attributes', \implode(', ', $attributes));
	}
}
