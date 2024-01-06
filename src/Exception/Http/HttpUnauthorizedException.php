<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\Exception\Http;

use Sunrise\Http\Router\Exception\HttpException;
use Throwable;

/**
 * HTTP Unauthorized Exception
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/401
 *
 * @since 3.0.0
 */
class HttpUnauthorizedException extends HttpException
{

    /**
     * The error's default message
     *
     * @var string
     */
    // phpcs:ignore Generic.Files.LineLength
    public const DEFAULT_MESSAGE = 'The request could not processed due to insufficient or invalid authentication credentials.';

    /**
     * Constructor of the class
     *
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(self::STATUS_UNAUTHORIZED, $message ?? self::DEFAULT_MESSAGE, $code, $previous);
    }
}
