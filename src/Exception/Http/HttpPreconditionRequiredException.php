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
 * HTTP Precondition Required Exception
 *
 * The origin server requires the request to be conditional. This response is intended to prevent the 'lost update'
 * problem, where a client GETs a resource's state, modifies it and PUTs it back to the server, when meanwhile a third
 * party has modified the state on the server, leading to a conflict.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/428
 *
 * @since 3.0.0
 */
class HttpPreconditionRequiredException extends HttpException
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
        $message ??= 'Precondition Required';

        parent::__construct(self::STATUS_PRECONDITION_REQUIRED, $message, $code, $previous);
    }
}
