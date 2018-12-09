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
use RuntimeException;

/**
 * MethodNotAllowedException
 */
class MethodNotAllowedException extends RuntimeException
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
	 * @param array $allowedMethods
	 */
	public function __construct(array $allowedMethods)
	{
		$this->allowedMethods = $allowedMethods;

		parent::__construct('Method not allowed.');
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
