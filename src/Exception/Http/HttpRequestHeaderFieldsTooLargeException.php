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
 * HTTP Request Header Fields Too Large Exception
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/431
 *
 * @since 3.0.0
 */
class HttpRequestHeaderFieldsTooLargeException extends HttpException
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
        $message ??= 'Request Header Fields Too Large';

        parent::__construct(self::STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE, $message, $code, $previous);
    }
}