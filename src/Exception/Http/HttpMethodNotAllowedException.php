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

use Stringable;
use Sunrise\Http\Router\Exception\HttpException;
use Throwable;

/**
 * HTTP Method Not Allowed Exception
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/405
 *
 * @since 3.0.0
 */
class HttpMethodNotAllowedException extends HttpException
{
    public const DEFAULT_MESSAGE = 'The request could not be processed using the requested HTTP method.';

    // phpcs:ignore Generic.Files.LineLength
    public function __construct(private array $allowedMethods, ?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(self::STATUS_METHOD_NOT_ALLOWED, $message ?? self::DEFAULT_MESSAGE, $code, $previous);

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Allow
        $this->addHeaderField('Allow', ...$allowedMethods);
    }

    /**
     * @return list<Stringable|string>
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
