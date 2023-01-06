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
 * HTTP Forbidden Exception
 *
 * The client does not have access rights to the content; that is, it is unauthorized, so the server is refusing to give
 * the requested resource. Unlike 401 Unauthorized, the client's identity is known to the server.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/403
 *
 * @since 3.0.0
 */
class HttpForbiddenException extends HttpException
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
        $message ??= 'Forbidden';

        parent::__construct(self::STATUS_FORBIDDEN, $message, $code, $previous);
    }
}
