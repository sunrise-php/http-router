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
 * HTTP Service Unavailable Exception
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/503
 *
 * @since 3.0.0
 */
class HttpServiceUnavailableException extends HttpException
{

    /**
     * The error's default message
     *
     * @var string
     */
    // phpcs:ignore Generic.Files.LineLength
    public const DEFAULT_MESSAGE = 'The server is currently unable to process the request due to temporary overloading or maintenance.';

    /**
     * Constructor of the class
     *
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(self::STATUS_SERVICE_UNAVAILABLE, $message ?? self::DEFAULT_MESSAGE, $code, $previous);
    }
}
