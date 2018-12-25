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
 * RouteInterface
 */
interface RouteInterface
{

	/**
	 * Adds the given prefix to the route path
	 *
	 * @param string $prefix
	 *
	 * @return RouteInterface
	 */
	public function addPrefix(string $prefix) : RouteInterface;

	/**
	 * Adds the given pattern to the route
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return RouteInterface
	 */
	public function addPattern(string $name, string $value) : RouteInterface;

	/**
	 * Adds the given middleware to the route middleware stack
	 *
	 * @param MiddlewareInterface $middleware
	 *
	 * @return RouteInterface
	 */
	public function addMiddleware(MiddlewareInterface $middleware) : RouteInterface;

	/**
	 * Gets the route ID
	 *
	 * @return string
	 */
	public function getId() : string;

	/**
	 * Gets the route path
	 *
	 * @return string
	 */
	public function getPath() : string;

	/**
	 * Gets the route methods
	 *
	 * @return string[]
	 */
	public function getMethods() : array;

	/**
	 * Gets the route patterns
	 *
	 * @return array
	 */
	public function getPatterns() : array;

	/**
	 * Gets the route attributes
	 *
	 * @return array
	 */
	public function getAttributes() : array;

	/**
	 * Gets the route middleware stack
	 *
	 * @return MiddlewareInterface[]
	 */
	public function getMiddlewareStack() : array;

	/**
	 * Returns the route clone with the given attributes
	 *
	 * @param array $attributes
	 *
	 * @return RouteInterface
	 */
	public function withAttributes(array $attributes) : RouteInterface;
}
