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

/**
 * HttpExceptionInterface
 */
interface HttpExceptionInterface
{

	/**
	 * Gets Server Request instance
	 *
	 * @return ServerRequestInterface
	 */
	public function getRequest() : ServerRequestInterface;
}
