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
use Throwable;

/**
 * ExceptionInterface
 */
interface ExceptionInterface extends Throwable
{

    /**
     * Gets the exception context
     *
     * @return array
     */
    public function getContext() : array;

    /**
     * Gets a value from the exception context by the given key
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function fromContext($key, $default);
}
