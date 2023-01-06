<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Exception\Http;

/**
 * Import classes
 */
use Throwable;

/**
 * HTTP Request Header Fields Too Large Exception
 *
 * The server is unwilling to process the request because its header fields are too large. The request may be
 * resubmitted after reducing the size of the request header fields.
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
     * @param ?string $message
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message ??= 'Request Header Fields Too Large';

        parent::__construct(self::STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE, $message, $code, $previous);
    }
}
