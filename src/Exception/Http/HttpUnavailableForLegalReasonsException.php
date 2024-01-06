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
 * HTTP Unavailable For Legal Reasons Exception
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/451
 *
 * @since 3.0.0
 */
class HttpUnavailableForLegalReasonsException extends HttpException
{

    /**
     * The error's default message
     *
     * @var string
     */
    public const DEFAULT_MESSAGE = 'The request could not be processed due to legal restrictions.';

    /**
     * Constructor of the class
     *
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        // phpcs:ignore Generic.Files.LineLength
        parent::__construct(self::STATUS_UNAVAILABLE_FOR_LEGAL_REASONS, $message ?? self::DEFAULT_MESSAGE, $code, $previous);
    }
}
