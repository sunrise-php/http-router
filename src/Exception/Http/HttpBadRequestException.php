<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\Exception\Http;

use Sunrise\Http\Router\Exception\HttpException;
use Throwable;

/**
 * HTTP Bad Request Exception
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/400
 *
 * @since 3.0.0
 */
class HttpBadRequestException extends HttpException
{

    /**
     * Constructor of the class
     *
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message ??= 'The request couldn‘t be processed due to malformed syntax or invalid parameters.';

        parent::__construct(self::STATUS_BAD_REQUEST, $message, $code, $previous);

        $this->setReasonPhrase('Bad Request');
    }
}
