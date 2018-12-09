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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * RouterInterface
 */
interface RouterInterface extends RequestHandlerInterface
{

	/**
	 * Adds a new route to the router map
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function add(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to HEAD requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function head(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to GET requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function get(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to POST requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function post(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to PUT requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function put(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to PATCH requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function patch(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to DELETE requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function delete(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to PURGE requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function purge(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to OPTIONS requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function options(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to TRACE requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function trace(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to CONNECT requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function connect(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to safe requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function safe(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new route to the router map that will respond to any requests
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 *
	 * @return RouteInterface
	 */
	public function any(string $id, string $path, callable $action) : RouteInterface;

	/**
	 * Adds a new middleware to the router middleware stack
	 *
	 * @param MiddlewareInterface $middleware
	 *
	 * @return RouterInterface
	 */
	public function middleware(MiddlewareInterface $middleware) : RouterInterface;

	/**
	 * Looks for a route that matches the given request
	 *
	 * @param ServerRequestInterface $request
	 *
	 * @return RouteInterface
	 *
	 * @throws Exception\MethodNotAllowedException
	 *         If the route found does not support the requested HTTP method.
	 *
	 * @throws Exception\PageNotFoundException
	 *         If a route was not matched.
	 */
	public function match(ServerRequestInterface $request) : RouteInterface;
}
