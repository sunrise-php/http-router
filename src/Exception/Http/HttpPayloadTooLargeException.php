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
 * HTTP Payload Too Large Exception
 *
 * Request entity is larger than limits defined by server. The server might close the connection or return an
 * Retry-After header field.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/413
 *
 * @since 3.0.0
 */
class HttpPayloadTooLargeException extends HttpException
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
        $message ??= 'Payload Too Large';

        parent::__construct(self::STATUS_PAYLOAD_TOO_LARGE, $message, $code, $previous);
    }
}
