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
 * Import functions
 */
use function implode;

/**
 * MethodNotAllowedException
 */
class MethodNotAllowedException extends Exception
{

    /**
     * Gets a method
     *
     * @return string
     *
     * @since 2.9.0
     */
    public function getMethod() : string
    {
        return $this->fromContext('method', '');
    }

    /**
     * Gets allowed methods
     *
     * @return string[]
     */
    public function getAllowedMethods() : array
    {
        return $this->fromContext('allowed', []);
    }

    /**
     * Gets joined allowed methods
     *
     * @return string
     */
    public function getJoinedAllowedMethods() : string
    {
        return implode(',', $this->getAllowedMethods());
    }
}
