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
 * HTTP Service Unavailable Exception
 *
 * The server is not ready to handle the request. Common causes are a server that is down for maintenance or that is
 * overloaded. Note that together with this response, a user-friendly page explaining the problem should be sent. This
 * response should be used for temporary conditions and the Retry-After HTTP header should, if possible, contain the
 * estimated time before the recovery of the service. The webmaster must also take care about the caching-related
 * headers that are sent along with this response, as these temporary condition responses should usually not be cached.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/503
 *
 * @since 3.0.0
 */
class HttpServiceUnavailableException extends HttpException
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
        $message ??= 'Service Unavailable';

        parent::__construct(self::STATUS_SERVICE_UNAVAILABLE, $message, $code, $previous);
    }
}
