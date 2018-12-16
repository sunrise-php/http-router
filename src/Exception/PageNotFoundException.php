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
 * PageNotFoundException
 */
class PageNotFoundException extends HttpException
{

	/**
	 * Constructor of the class
	 *
	 * @param ServerRequestInterface $request
	 */
	public function __construct(ServerRequestInterface $request)
	{
		$response = (new ResponseFactory)->createResponse(404);

		parent::__construct($request, $response);
	}
}
