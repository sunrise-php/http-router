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
 * Route
 */
class Route implements RouteInterface
{

	/**
	 * The route ID
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The route path
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * The route methods
	 *
	 * @var string[]
	 */
	protected $methods = [];

	/**
	 * The route patterns
	 *
	 * @var array
	 */
	protected $patterns = [];

	/**
	 * The route attributes
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * The route middleware stack
	 *
	 * @var MiddlewareInterface[]
	 */
	protected $middlewareStack = [];

	/**
	 * Constructor of the class
	 *
	 * @param string $id
	 * @param string $path
	 * @param string[] $methods
	 */
	public function __construct(string $id, string $path, array $methods)
	{
		$this->id = $id;

		$this->path = $path;

		foreach ($methods as $method)
		{
			$this->methods[] = \strtoupper($method);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addPrefix(string $prefix) : RouteInterface
	{
		$this->path = $prefix . $this->path;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addPattern(string $name, string $value) : RouteInterface
	{
		$this->patterns[$name] = $value;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMiddleware(MiddlewareInterface $middleware) : RouteInterface
	{
		$this->middlewareStack[] = $middleware;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getId() : string
	{
		return $this->id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPath() : string
	{
		return $this->path;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMethods() : array
	{
		return $this->methods;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPatterns() : array
	{
		return $this->patterns;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAttributes() : array
	{
		return $this->attributes;
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
	public function withAttributes(array $attributes) : RouteInterface
	{
		$clone = clone $this;

		$clone->attributes = \array_merge($clone->attributes, $attributes);

		return $clone;
	}
}
