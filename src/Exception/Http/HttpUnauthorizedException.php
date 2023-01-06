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
 * HTTP Unauthorized Exception
 *
 * Although the HTTP standard specifies "unauthorized", semantically this response means "unauthenticated". That is, the
 * client must authenticate itself to get the requested response.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/401
 *
 * @since 3.0.0
 */
class HttpUnauthorizedException extends HttpException
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
        $message ??= 'Unauthorized';

        parent::__construct(self::STATUS_UNAUTHORIZED, $message, $code, $previous);
    }
}
