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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HttpException
 */
class HttpException extends \RuntimeException implements HttpExceptionInterface
{

	/**
	 * Server Request instance
	 *
	 * @var ServerRequestInterface
	 */
	protected $request;

	/**
	 * Response instance
	 *
	 * @var ResponseInterface
	 */
	protected $response;

	/**
	 * Constructor of the class
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param null|\Throwable $previous
	 */
	public function __construct(ServerRequestInterface $request, ResponseInterface $response, \Throwable $previous = null)
	{
		$this->request = $request;

		$this->response = $response;

		parent::__construct(\sprintf('[%d] %s', $response->getStatusCode(), $response->getReasonPhrase()), 0, $previous);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequest() : ServerRequestInterface
	{
		return $this->request;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getResponse() : ResponseInterface
	{
		return $this->response;
	}
}
