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
 * BadRequestException
 */
class BadRequestException extends HttpException
{

	/**
	 * Constructor of the class
	 *
	 * @param ServerRequestInterface $request
	 */
	public function __construct(ServerRequestInterface $request)
	{
		parent::__construct($request, (new ResponseFactory)->createResponse(400));
	}
}
