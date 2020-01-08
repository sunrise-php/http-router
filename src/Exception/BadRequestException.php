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
 * BadRequestException
 */
class BadRequestException extends Exception
{

    /**
     * Gets violations
     *
     * @return array
     */
    public function getViolations() : array
    {
        return $this->fromContext('violations', []);
    }
}
