<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\PageNotFoundException;

/**
 * Router
 */
class Router extends RouteCollection implements RouterInterface
{

	/**
	 * {@inheritDoc}
	 */
	public function match(ServerRequestInterface $request) : RouteInterface
	{
		$allow = [];

		foreach ($this->getRoutes() as $route)
		{
			$regex = route_regex($route->getPath(), $route->getPatterns());

			if (\preg_match($regex, $request->getUri()->getPath(), $attributes))
			{
				$allow = \array_merge($allow, $route->getMethods());

				if (\in_array($request->getMethod(), $route->getMethods()))
				{
					return $route->withAttributes($attributes);
				}
			}
		}

		if (! empty($allow))
		{
			throw new MethodNotAllowedException($request, $allow);
		}

		throw new PageNotFoundException($request);
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(ServerRequestInterface $request) : ResponseInterface
	{
		$route = $this->match($request);

		foreach ($route->getAttributes() as $name => $value)
		{
			$request = $request->withAttribute($name, $value);
		}

		$request = $request->withAttribute('@route', $route->getId());

		$requestHandler = new RequestHandler();

		foreach ($this->getMiddlewareStack() as $middleware)
		{
			$requestHandler->add($middleware);
		}

		foreach ($route->getMiddlewareStack() as $middleware)
		{
			$requestHandler->add($middleware);
		}

		$requestHandler->add($route);

		return $requestHandler->handle($request);
	}
}
