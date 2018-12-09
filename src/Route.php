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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
	 * The route action
	 *
	 * @var callable
	 */
	protected $action;

	/**
	 * The route methods
	 *
	 * @var array
	 */
	protected $methods = [];

	/**
	 * The route patterns
	 *
	 * @var array
	 */
	protected $patterns = [];

	/**
	 * The route middleware stack
	 *
	 * @var MiddlewareInterface[]
	 */
	protected $middlewareStack = [];

	/**
	 * The route attributes
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * {@inheritDoc}
	 */
	public function __construct(string $id, string $path, callable $action)
	{
		$this->id = $id;

		$this->path = $path;

		$this->action = $action;
	}

	/**
	 * {@inheritDoc}
	 */
	public function method(string $method) : RouteInterface
	{
		$this->methods[] = \strtoupper($method);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function pattern(string $name, string $value) : RouteInterface
	{
		$this->patterns[$name] = $value;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function middleware(MiddlewareInterface $middleware) : RouteInterface
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
	public function getAction() : callable
	{
		return $this->action;
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
	public function getMiddlewareStack() : array
	{
		return $this->middlewareStack;
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
	public function withAttributes(array $attributes) : RouteInterface
	{
		$clone = clone $this;

		$clone->attributes = $attributes;

		return $clone;
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
	{
		$action = $this->getAction();

		$response = $handler->handle($request);

		return \call_user_func($action, $request, $response);
	}
}
