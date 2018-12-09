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
interface RouteInterface extends MiddlewareInterface
{

	/**
	 * Constructor of the class
	 *
	 * @param string $id
	 * @param string $path
	 * @param callable $action
	 */
	public function __construct(string $id, string $path, callable $action);

	/**
	 * Adds a new method to the route
	 *
	 * @param string $method
	 *
	 * @return RouteInterface
	 */
	public function method(string $method) : RouteInterface;

	/**
	 * Adds a new pattern to the route
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return RouteInterface
	 */
	public function pattern(string $name, string $value) : RouteInterface;

	/**
	 * Adds a new middleware to the route middleware stack
	 *
	 * @param MiddlewareInterface $middleware
	 *
	 * @return RouteInterface
	 */
	public function middleware(MiddlewareInterface $middleware) : RouteInterface;

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
	 * Gets the route action
	 *
	 * @return callable
	 */
	public function getAction() : callable;

	/**
	 * Gets the route methods
	 *
	 * @return array
	 */
	public function getMethods() : array;

	/**
	 * Gets the route patterns
	 *
	 * @return array
	 */
	public function getPatterns() : array;

	/**
	 * Gets the route middleware stack
	 *
	 * @return MiddlewareInterface[]
	 */
	public function getMiddlewareStack() : array;

	/**
	 * Gets the route attributes
	 *
	 * @return array
	 */
	public function getAttributes() : array;

	/**
	 * Creates a new route instance with the given attributes
	 *
	 * @param array $attributes
	 *
	 * @return RouteInterface
	 */
	public function withAttributes(array $attributes) : RouteInterface;
}
