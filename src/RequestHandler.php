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
use Sunrise\Http\Message\ResponseFactory;

/**
 * RequestHandler
 *
 * @link https://www.php-fig.org/psr/psr-15/
 * @link https://www.php-fig.org/psr/psr-15/meta/
 */
class RequestHandler implements RequestHandlerInterface
{

	/**
	 * The request handler middleware stack
	 *
	 * @var MiddlewareInterface[]
	 */
	protected $middlewareStack = [];

	/**
	 * Adds the given middleware to the request handler middleware stack
	 *
	 * @param MiddlewareInterface $middleware
	 *
	 * @return RequestHandlerInterface
	 */
	public function add(MiddlewareInterface $middleware) : RequestHandlerInterface
	{
		$this->middlewareStack[] = $middleware;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(ServerRequestInterface $request) : ResponseInterface
	{
		if (empty($this->middlewareStack))
		{
			return (new ResponseFactory)->createResponse(200);
		}

		$middleware = \array_shift($this->middlewareStack);

		return $middleware->process($request, $this);
	}
}
