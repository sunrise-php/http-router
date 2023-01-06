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
 * HTTP Proxy Authentication Required Exception
 *
 * This is similar to 401 Unauthorized but authentication is needed to be done by a proxy.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/407
 *
 * @since 3.0.0
 */
class HttpProxyAuthenticationRequiredException extends HttpException
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
        $message ??= 'Proxy Authentication Required';

        parent::__construct(self::STATUS_PROXY_AUTHENTICATION_REQUIRED, $message, $code, $previous);
    }
}
