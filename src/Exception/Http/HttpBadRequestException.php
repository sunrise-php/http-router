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
 * HTTP Bad Request Exception
 *
 * The server cannot or will not process the request due to something that is perceived to be a client error (e.g.,
 * malformed request syntax, invalid request message framing, or deceptive request routing).
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/400
 *
 * @since 3.0.0
 */
class HttpBadRequestException extends HttpException
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
        $message ??= 'Bad Request';

        parent::__construct(self::STATUS_BAD_REQUEST, $message, $code, $previous);
    }
}
