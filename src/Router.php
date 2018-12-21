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
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;

/**
 * Router
 */
class Router implements RouterInterface
{

	/**
	 * The router map
	 *
	 * @var RouteCollectionInterface
	 */
	protected $routes;

	/**
	 * The router middleware stack
	 *
	 * @var MiddlewareInterface[]
	 */
	protected $middlewareStack = [];

	/**
	 * {@inheritDoc}
	 */
	public function __construct(RouteCollectionInterface $routes)
	{
		$this->routes = $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMiddleware(MiddlewareInterface $middleware) : RouterInterface
	{
		$this->middlewareStack[] = $middleware;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMiddlewareStack() : array
	{
		return $this->middlewareStack;
	}

	/**
	 * {@inheritDoc}
	 */
	public function match(ServerRequestInterface $request) : RouteInterface
	{
		$allowed = [];

		foreach ($this->routes->getRoutes() as $route)
		{
			$regex = route_regex($route->getPath(), $route->getPatterns());

			if (\preg_match($regex, $request->getUri()->getPath(), $attributes))
			{
				$allowed = \array_merge($allowed, $route->getMethods());

				if (\in_array($request->getMethod(), $route->getMethods()))
				{
					return $route->withAttributes($attributes);
				}
			}
		}

		if (! empty($allowed))
		{
			throw new MethodNotAllowedException($request, $allowed);
		}

		throw new RouteNotFoundException($request);
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(ServerRequestInterface $request) : ResponseInterface
	{
		$route = $this->match($request);

		$requestHandler = new RequestHandler();

		foreach ($this->middlewareStack as $middleware)
		{
			$requestHandler->add($middleware);
		}

		foreach ($route->getMiddlewareStack() as $middleware)
		{
			$requestHandler->add($middleware);
		}

		foreach ($route->getAttributes() as $name => $value)
		{
			$request = $request->withAttribute($name, $value);
		}

		$request = $request->withAttribute('@route', $route->getId());

		return $requestHandler->handle($request);
	}
}
