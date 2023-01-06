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
 * HTTP Range Not Satisfiable Exception
 *
 * The range specified by the Range header field in the request cannot be fulfilled. It's possible that the range is
 * outside the size of the target URI's data.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/416
 *
 * @since 3.0.0
 */
class HttpRangeNotSatisfiableException extends HttpException
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
        $message ??= 'Range Not Satisfiable';

        parent::__construct(self::STATUS_RANGE_NOT_SATISFIABLE, $message, $code, $previous);
    }
}
