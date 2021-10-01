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
     * Gets errors
     *
     * @return mixed
     *
     * @since 2.9.0
     */
    public function getErrors()
    {
        return $this->fromContext('errors', []);
    }

    /**
     * Gets violations
     *
     * @return mixed
     *
     * @deprecated 2.9.0 Use the getErrors method.
     */
    public function getViolations()
    {
        return $this->fromContext('violations', []);
    }
}
