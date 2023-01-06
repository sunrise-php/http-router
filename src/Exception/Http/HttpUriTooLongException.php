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
 * HTTP URI Too Long Exception
 *
 * The URI requested by the client is longer than the server is willing to interpret.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/414
 *
 * @since 3.0.0
 */
class HttpUriTooLongException extends HttpException
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
        $message ??= 'URI Too Long';

        parent::__construct(self::STATUS_URI_TOO_LONG, $message, $code, $previous);
    }
}
