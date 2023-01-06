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
 * HTTP Upgrade Required Exception
 *
 * The server refuses to perform the request using the current protocol but might be willing to do so after the client
 * upgrades to a different protocol. The server sends an Upgrade header in a 426 response to indicate the required
 * protocol(s).
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/426
 *
 * @since 3.0.0
 */
class HttpUpgradeRequiredException extends HttpException
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
        $message ??= 'Upgrade Required';

        parent::__construct(self::STATUS_UPGRADE_REQUIRED, $message, $code, $previous);
    }
}
