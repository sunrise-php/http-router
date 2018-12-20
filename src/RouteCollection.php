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
	 * {@inheritDoc}
	 */
	public function addRoute(RouteInterface $route) : RouteCollectionInterface
	{
		$this->routes[] = $route;

		return $this;
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
	public function route(string $id, string $path, array $methods) : RouteInterface
	{
		$route = new Route($id, $path, $methods);

		$this->addRoute($route);

		return $route;
	}

	/**
	 * {@inheritDoc}
	 */
	public function head(string $id, string $path) : RouteInterface
	{
		$methods[] = RequestMethodInterface::METHOD_HEAD;

		return $this->route($id, $path, $methods);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $id, string $path) : RouteInterface
	{
		$methods[] = RequestMethodInterface::METHOD_GET;

		return $this->route($id, $path, $methods);
	}

	/**
	 * {@inheritDoc}
	 */
	public function post(string $id, string $path) : RouteInterface
	{
		$methods[] = RequestMethodInterface::METHOD_POST;

		return $this->route($id, $path, $methods);
	}

	/**
	 * {@inheritDoc}
	 */
	public function put(string $id, string $path) : RouteInterface
	{
		$methods[] = RequestMethodInterface::METHOD_PUT;

		return $this->route($id, $path, $methods);
	}

	/**
	 * {@inheritDoc}
	 */
	public function patch(string $id, string $path) : RouteInterface
	{
		$methods[] = RequestMethodInterface::METHOD_PATCH;

		return $this->route($id, $path, $methods);
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(string $id, string $path) : RouteInterface
	{
		$methods[] = RequestMethodInterface::METHOD_DELETE;

		return $this->route($id, $path, $methods);
	}

	/**
	 * {@inheritDoc}
	 */
	public function purge(string $id, string $path) : RouteInterface
	{
		$methods[] = RequestMethodInterface::METHOD_PURGE;

		return $this->route($id, $path, $methods);
	}

	/**
	 * {@inheritDoc}
	 */
	public function safe(string $id, string $path) : RouteInterface
	{
		$methods[] = RequestMethodInterface::METHOD_HEAD;
		$methods[] = RequestMethodInterface::METHOD_GET;

		return $this->route($id, $path, $methods);
	}

	/**
	 * {@inheritDoc}
	 */
	public function any(string $id, string $path) : RouteInterface
	{
		$methods[] = RequestMethodInterface::METHOD_HEAD;
		$methods[] = RequestMethodInterface::METHOD_GET;
		$methods[] = RequestMethodInterface::METHOD_POST;
		$methods[] = RequestMethodInterface::METHOD_PUT;
		$methods[] = RequestMethodInterface::METHOD_PATCH;
		$methods[] = RequestMethodInterface::METHOD_DELETE;
		$methods[] = RequestMethodInterface::METHOD_PURGE;

		return $this->route($id, $path, $methods);
	}

	/**
	 * {@inheritDoc}
	 */
	public function group(string $prefix, callable $callback) : void
	{
		$collection = new self;

		$callback($collection);

		foreach ($collection->getRoutes() as $route)
		{
			$route->addPrefix($prefix);

			$this->addRoute($route);
		}
	}
}
