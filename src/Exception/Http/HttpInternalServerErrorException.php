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
 * HTTP Internal Server Error Exception
 *
 * The server has encountered a situation it does not know how to handle.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/500
 *
 * @since 3.0.0
 */
class HttpInternalServerErrorException extends HttpException
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
        $message ??= 'Internal Server Error';

        parent::__construct(self::STATUS_INTERNAL_SERVER_ERROR, $message, $code, $previous);
    }
}
