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
 * HTTP Not Found Exception
 *
 * The server cannot find the requested resource. In the browser, this means the URL is not recognized. In an API, this
 * can also mean that the endpoint is valid but the resource itself does not exist. Servers may also send this response
 * instead of 403 Forbidden to hide the existence of a resource from an unauthorized client. This response code is
 * probably the most well known due to its frequent occurrence on the web.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404
 *
 * @since 3.0.0
 */
class HttpNotFoundException extends HttpException
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
        $message ??= 'Not Found';

        parent::__construct(self::STATUS_NOT_FOUND, $message, $code, $previous);
    }
}
