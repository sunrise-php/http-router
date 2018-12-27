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
	 * Sets the given ID to the route
	 *
	 * @param string $id
	 *
	 * @return RouteInterface
	 */
	public function setId(string $id) : RouteInterface;

	/**
	 * Sets the given path to the route
	 *
	 * @param string $path
	 *
	 * @return RouteInterface
	 */
	public function setPath(string $path) : RouteInterface;

	/**
	 * Adds the given prefix to the route path
	 *
	 * @param string $prefix
	 *
	 * @return RouteInterface
	 */
	public function addPrefix(string $prefix) : RouteInterface;

	/**
	 * Adds the given suffix to the route path
	 *
	 * @param string $suffix
	 *
	 * @return RouteInterface
	 */
	public function addSuffix(string $suffix) : RouteInterface;

	/**
	 * Adds the given method to the route
	 *
	 * @param string $method
	 *
	 * @return RouteInterface
	 */
	public function addMethod(string $method) : RouteInterface;

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
	 *
	 * @throws \RuntimeException If the route ID is missing
	 */
	public function getId() : string;

	/**
	 * Gets the route path
	 *
	 * @return string
	 *
	 * @throws \RuntimeException If the route path is missing
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
