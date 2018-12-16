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
use Psr\Http\Server\MiddlewareInterface;

/**
 * RouteCollection
 */
class RouteCollection implements RouteCollectionInterface
{

	/**
	 * The collection routes
	 *
	 * @var RouteInterface[]
	 */
	protected $routes = [];

	/**
	 * The collection middleware stack
	 *
	 * @var MiddlewareInterface[]
	 */
	protected $middlewareStack = [];

	/**
	 * {@inheritDoc}
	 */
	public function group(string $prefix, callable $callback) : void
	{
		$collection = new self;

		$callback($collection);

		if ($collection->count() > 0)
		{
			foreach ($collection->getRoutes() as $route)
			{
				$route->prefix($prefix);

				foreach ($collection->getMiddlewareStack() as $middleware)
				{
					$route->middleware($middleware);
				}

				$this->addRoute($route);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function add(string $id, string $path, callable $action, array $methods = []) : RouteInterface
	{
		$route = new Route($id, $path, $action);

		foreach ($methods as $method)
		{
			$route->method($method);
		}

		$this->addRoute($route);

		return $route;
	}

	/**
	 * {@inheritDoc}
	 */
	public function head(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_HEAD,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_GET,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function post(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_POST,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function put(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_PUT,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function patch(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_PATCH,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_DELETE,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function purge(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_PURGE,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function options(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_OPTIONS,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function trace(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_TRACE,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function connect(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_CONNECT,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function safe(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_HEAD,
			RequestMethodInterface::METHOD_GET,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function any(string $id, string $path, callable $action) : RouteInterface
	{
		return $this->add($id, $path, $action, [
			RequestMethodInterface::METHOD_HEAD,
			RequestMethodInterface::METHOD_GET,
			RequestMethodInterface::METHOD_POST,
			RequestMethodInterface::METHOD_PUT,
			RequestMethodInterface::METHOD_PATCH,
			RequestMethodInterface::METHOD_DELETE,
			RequestMethodInterface::METHOD_PURGE,
			RequestMethodInterface::METHOD_OPTIONS,
			RequestMethodInterface::METHOD_TRACE,
			RequestMethodInterface::METHOD_CONNECT,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function middleware(MiddlewareInterface $middleware) : RouteCollectionInterface
	{
		$this->middlewareStack[] = $middleware;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addRoute(RouteInterface $route) : RouteCollectionInterface
	{
		$this->routes[$route->getId()] = $route;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoute(string $routeId) : ?RouteInterface
	{
		return $this->routes[$routeId] ?? null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoutes() : array
	{
		return $this->routes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMiddlewareStack() : array
	{
		return $this->middlewareStack;
	}

	/**
	 * Gets the number of routes in the collection
	 *
	 * @return int
	 */
	public function count()
	{
		return \count($this->routes);
	}
}
