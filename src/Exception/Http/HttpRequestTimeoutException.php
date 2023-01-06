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
 * HTTP Request Timeout Exception
 *
 * This response is sent on an idle connection by some servers, even without any previous request by the client. It
 * means that the server would like to shut down this unused connection. This response is used much more since some
 * browsers, like Chrome, Firefox 27+, or IE9, use HTTP pre-connection mechanisms to speed up surfing. Also note that
 * some servers merely shut down the connection without sending this message.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/408
 *
 * @since 3.0.0
 */
class HttpRequestTimeoutException extends HttpException
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
        $message ??= 'Request Timeout';

        parent::__construct(self::STATUS_REQUEST_TIMEOUT, $message, $code, $previous);
    }
}
