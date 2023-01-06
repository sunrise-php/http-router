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
 * HTTP Unprocessable Entity Exception
 *
 * The request was well-formed but was unable to be followed due to semantic errors.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/422
 *
 * @since 3.0.0
 */
class HttpUnprocessableEntityException extends HttpException
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
        $message ??= 'Unprocessable Entity';

        parent::__construct(self::STATUS_UNPROCESSABLE_ENTITY, $message, $code, $previous);
    }
}
