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
	 * Adds the given middleware to the router middleware stack
	 *
	 * @param MiddlewareInterface $middleware
	 *
	 * @return RouterInterface
	 */
	public function addMiddleware(MiddlewareInterface $middleware) : RouterInterface;

	/**
	 * Gets the router middleware stack
	 *
	 * @return MiddlewareInterface[]
	 */
	public function getMiddlewareStack() : array;

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
	 * @throws Exception\RouteNotFoundException
	 *         If a route was not matched.
	 */
	public function match(ServerRequestInterface $request) : RouteInterface;
}
