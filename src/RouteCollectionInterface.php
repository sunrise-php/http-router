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
use Psr\Http\Server\MiddlewareInterface;

/**
 * RouteCollectionInterface
 */
interface RouteCollectionInterface extends \Countable
{

	/**
	 * Route grouping
	 *
	 * @param string $prefix
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function group(string $prefix, callable $callback) : void;

	/**
	 * Adds a new route to the collection
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 * @param array $methods
	 *
	 * @return RouteInterface
	 */
	public function add(string $id, string $path, callable $action, array $methods = []) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to HEAD requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function head(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to GET requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function get(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to POST requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function post(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to PUT requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function put(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to PATCH requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function patch(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to DELETE requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function delete(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to PURGE requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function purge(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to OPTIONS requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function options(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to TRACE requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function trace(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to CONNECT requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function connect(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to safe requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function safe(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the collection that will respond to any requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function any(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds the given middleware to the collection middleware stack
	 *
	 * @param MiddlewareInterface $middleware
	 *
	 * @return RouteCollectionInterface
	 */
	public function middleware(MiddlewareInterface $middleware) : RouteCollectionInterface;

	/**
	 * Adds the given route to the collection
	 *
	 * @param RouteInterface $route
	 *
	 * @return RouteCollectionInterface
	 */
	public function addRoute(RouteInterface $route) : RouteCollectionInterface;

	/**
	 * Gets a route for the given ID from the collection
	 *
	 * @param string $routeId
	 *
	 * @return null|RouteInterface
	 */
	public function getRoute(string $routeId) : ?RouteInterface;

	/**
	 * Gets the collection routes
	 *
	 * @return RouteInterface[]
	 */
	public function getRoutes() : array;

	/**
	 * Gets the collection middleware stack
	 *
	 * @return MiddlewareInterface[]
	 */
	public function getMiddlewareStack() : array;
}
