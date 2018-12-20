<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Exception;

/**
 * Import classes
 */
use RuntimeException, Throwable;
use Psr\Http\Message\ServerRequestInterface;

/**
 * RouteNotFoundException
 */
class RouteNotFoundException extends RuntimeException implements HttpExceptionInterface
{

	/**
	 * Server Request instance
	 *
	 * @var ServerRequestInterface
	 */
	protected $request;

	/**
	 * Constructor of the class
	 *
	 * @param ServerRequestInterface $request
	 * @param int $code
	 * @param null|Throwable $previous
	 */
	public function __construct(ServerRequestInterface $request, int $code = 0, Throwable $previous = null)
	{
		$this->request = $request;

		parent::__construct('Unable to find a route for the request', $code, $previous);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequest() : ServerRequestInterface
	{
		return $this->request;
	}
}
