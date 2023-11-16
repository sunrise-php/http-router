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
use Sunrise\Http\Router\Dictionary\ErrorSource;
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

    /**
     * Constructor of the class
     *
     * @param list<Stringable|string> $allowedMethods
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    // phpcs:ignore Generic.Files.LineLength
    public function __construct(private array $allowedMethods, ?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message ??= 'The request could not be processed using the requested HTTP method.';

        parent::__construct(self::STATUS_METHOD_NOT_ALLOWED, $message, $code, $previous);

        $this->setSource(ErrorSource::CLIENT_REQUEST_METHOD);

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Allow
        $this->addHeader('Allow', ...$allowedMethods);
    }

    /**
     * @return list<Stringable|string>
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
