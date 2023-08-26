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
 * HTTP Internal Server Error Exception
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
     * @param non-empty-string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message ??= 'The server encountered an unexpected condition that prevented it from fulfilling the request.';

        parent::__construct(self::STATUS_INTERNAL_SERVER_ERROR, $message, $code, $previous);

        $this->setReasonPhrase('Internal Server Error');
    }
}
