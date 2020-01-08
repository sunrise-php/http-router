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
 * UnsupportedMediaTypeException
 */
class UnsupportedMediaTypeException extends Exception
{

    /**
     * Gets a type
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->fromContext('type', '');
    }

    /**
     * Gets supported types
     *
     * @return string[]
     */
    public function getSupportedTypes() : array
    {
        return $this->fromContext('supported', []);
    }
}
