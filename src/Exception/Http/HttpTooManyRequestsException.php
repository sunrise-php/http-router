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
 * HTTP Too Many Requests Exception
 *
 * The user has sent too many requests in a given amount of time ("rate limiting").
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429
 *
 * @since 3.0.0
 */
class HttpTooManyRequestsException extends HttpException
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
        $message ??= 'Too Many Requests';

        parent::__construct(self::STATUS_TOO_MANY_REQUESTS, $message, $code, $previous);
    }
}
