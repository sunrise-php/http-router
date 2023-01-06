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
 * HTTP Misdirected Request Exception
 *
 * The request was directed at a server that is not able to produce a response. This can be sent by a server that is not
 * configured to produce responses for the combination of scheme and authority that are included in the request URI.
 *
 * @since 3.0.0
 */
class HttpMisdirectedRequestException extends HttpException
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
        $message ??= 'Misdirected Request';

        parent::__construct(self::STATUS_MISDIRECTED_REQUEST, $message, $code, $previous);
    }
}
