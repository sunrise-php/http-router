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
 * HTTP Too Early Exception
 *
 * Indicates that the server is unwilling to risk processing a request that might be replayed.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/425
 *
 * @since 3.0.0
 */
class HttpTooEarlyException extends HttpException
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
        $message ??= 'Too Early';

        parent::__construct(self::STATUS_TOO_EARLY, $message, $code, $previous);
    }
}