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
     * The error's default message
     *
     * @var string
     */
    // phpcs:ignore Generic.Files.LineLength
    public const DEFAULT_MESSAGE = 'The server encountered an unexpected condition that prevented it from fulfilling the request.';

    /**
     * Constructor of the class
     *
     * @param Throwable $error
     * @param string|null $message
     * @param int $code
     */
    public function __construct(Throwable $error, ?string $message = null, int $code = 0)
    {
        parent::__construct(self::STATUS_INTERNAL_SERVER_ERROR, $message ?? self::DEFAULT_MESSAGE, $code, $error);
    }

    /**
     * Gets the server error
     *
     * @return Throwable
     */
    public function getServerError(): Throwable
    {
        /** @var Throwable */
        return $this->getPrevious();
    }
}
