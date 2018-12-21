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
 * MethodNotAllowedException
 */
class MethodNotAllowedException extends RuntimeException implements HttpExceptionInterface
{

	/**
	 * Server Request instance
	 *
	 * @var ServerRequestInterface
	 */
	protected $request;

	/**
	 * Allowed HTTP methods
	 *
	 * @var string[]
	 */
	protected $allowedMethods;

	/**
	 * Constructor of the class
	 *
	 * @param ServerRequestInterface $request
	 * @param string[] $allowedMethods
	 * @param int $code
	 * @param null|Throwable $previous
	 */
	public function __construct(ServerRequestInterface $request, array $allowedMethods, int $code = 0, Throwable $previous = null)
	{
		$this->request = $request;

		$this->allowedMethods = $allowedMethods;

		parent::__construct('The requested resource is not available for the HTTP method', $code, $previous);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequest() : ServerRequestInterface
	{
		return $this->request;
	}

	/**
	 * Gets allowed HTTP methods
	 *
	 * @return string[]
	 */
	public function getAllowedMethods() : array
	{
		return $this->allowedMethods;
	}
}
