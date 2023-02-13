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
use Sunrise\Http\Router\Exception\HttpException;
use Throwable;

/**
 * HTTP Expectation Failed Exception
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/417
 *
 * @since 3.0.0
 */
class HttpExpectationFailedException extends HttpException
{

    /**
     * Constructor of the class
     *
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message ??= 'Expectation Failed';

        parent::__construct(self::STATUS_EXPECTATION_FAILED, $message, $code, $previous);
    }
}
