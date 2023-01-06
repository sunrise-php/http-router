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
 * HTTP Gone Exception
 *
 * This response is sent when the requested content has been permanently deleted from server, with no forwarding
 * address. Clients are expected to remove their caches and links to the resource. The HTTP specification intends this
 * status code to be used for "limited-time, promotional services". APIs should not feel compelled to indicate resources
 * that have been deleted with this status code.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/410
 *
 * @since 3.0.0
 */
class HttpGoneException extends HttpException
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
        $message ??= 'Gone';

        parent::__construct(self::STATUS_GONE, $message, $code, $previous);
    }
}
