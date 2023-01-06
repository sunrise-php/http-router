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
 * HTTP Payment Required Exception
 *
 * This response code is reserved for future use. The initial aim for creating this code was using it for digital
 * payment systems, however this status code is used very rarely and no standard convention exists.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/402
 *
 * @since 3.0.0
 */
class HttpPaymentRequiredException extends HttpException
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
        $message ??= 'Payment Required';

        parent::__construct(self::STATUS_PAYMENT_REQUIRED, $message, $code, $previous);
    }
}
