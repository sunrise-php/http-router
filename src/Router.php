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
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\PageNotFoundException;

/**
 * Router
 */
class Router implements RouterInterface
{

	/**
	 * The router map
	 *
	 * @var RouteInterface[]
	 */
	protected $map = [];

	/**
	 * The router middleware stack
	 *
	 * @var MiddlewareInterface[]
	 */
	protected $middlewareStack = [];

	/**
	 * {@inheritDoc}
	 */
	public function add(string $id, string $path, callable $action) : RouteInterface
	{
		$route = new Route($id, $path, $action);

		$this->map[$id] = $route;

		return $route;
	}

	/**
	 * {@inheritDoc}
	 */
	public function head(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_HEAD);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_GET);
	}

	/**
	 * {@inheritDoc}
	 */
	public function post(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_POST);
	}

	/**
	 * {@inheritDoc}
	 */
	public function put(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_PUT);
	}

	/**
	 * {@inheritDoc}
	 */
	public function patch(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_PATCH);
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_DELETE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function purge(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_PURGE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function options(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_OPTIONS);
	}

	/**
	 * {@inheritDoc}
	 */
	public function trace(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_TRACE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function connect(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_CONNECT);
	}

	/**
	 * {@inheritDoc}
	 */
	public function safe(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_HEAD)
			->method(RequestMethodInterface::METHOD_GET);
	}

	/**
	 * {@inheritDoc}
	 */
	public function any(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action)
			->method(RequestMethodInterface::METHOD_HEAD)
			->method(RequestMethodInterface::METHOD_GET)
			->method(RequestMethodInterface::METHOD_POST)
			->method(RequestMethodInterface::METHOD_PUT)
			->method(RequestMethodInterface::METHOD_PATCH)
			->method(RequestMethodInterface::METHOD_DELETE)
			->method(RequestMethodInterface::METHOD_PURGE)
			->method(RequestMethodInterface::METHOD_OPTIONS)
			->method(RequestMethodInterface::METHOD_TRACE)
			->method(RequestMethodInterface::METHOD_CONNECT);
	}

	/**
	 * {@inheritDoc}
	 */
	public function middleware(MiddlewareInterface $middleware) : RouterInterface
	{
		$this->middlewareStack[] = $middleware;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function match(ServerRequestInterface $request) : RouteInterface
	{
		$allowedVerbs = [];

		foreach ($this->map as $route)
		{
			$regex = route_regex($route->getPath(), $route->getPatterns());

			if (\preg_match($regex, $request->getUri()->getPath(), $attributes))
			{
				$allowedVerbs = \array_merge($allowedVerbs, $route->getMethods());

				if (\in_array($request->getMethod(), $route->getMethods()))
				{
					return $route->withAttributes($attributes);
				}
			}
		}

		if (! empty($allowedVerbs))
		{
			$allowedVerbs = \array_unique($allowedVerbs);

			throw new MethodNotAllowedException($request, $allowedVerbs);
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

		foreach ($this->middlewareStack as $middleware)
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
