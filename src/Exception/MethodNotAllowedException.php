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
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Message\ResponseFactory;

/**
 * MethodNotAllowedException
 */
class MethodNotAllowedException extends HttpException
{

	/**
	 * The request allowed methods
	 *
	 * @var array
	 */
	protected $allowedMethods;

	/**
	 * Constructor of the class
	 *
	 * @param ServerRequestInterface $request
	 * @param array $allowedMethods
	 */
	public function __construct(ServerRequestInterface $request, array $allowedMethods)
	{
		$response = (new ResponseFactory)->createResponse(405);

		$response = $response->withHeader('Allow', \implode(', ', $allowedMethods));

		parent::__construct($request, $response);

		$this->allowedMethods = $allowedMethods;
	}

	/**
	 * Gets the request allowed methods
	 *
	 * @return array
	 */
	public function getAllowedMethods() : array
	{
		return $this->allowedMethods;
	}
}
