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
 * HTTP Unsupported Media Type Exception
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/415
 *
 * @since 3.0.0
 */
class HttpUnsupportedMediaTypeException extends HttpException
{

    /**
     * The error's default message
     *
     * @var string
     */
    // phpcs:ignore Generic.Files.LineLength
    public const DEFAULT_MESSAGE = 'The request could not be processed due to an unsupported format of the request payload.';

    /**
     * Constructor of the class
     *
     * @param list<Stringable|string> $supportedMediaTypes
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    // phpcs:ignore Generic.Files.LineLength
    public function __construct(private array $supportedMediaTypes, ?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(self::STATUS_UNSUPPORTED_MEDIA_TYPE, $message ?? self::DEFAULT_MESSAGE, $code, $previous);

        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept
        $this->addHeaderField('Accept', ...$supportedMediaTypes);
    }

    /**
     * @return list<Stringable|string>
     */
    public function getSupportedMediaTypes(): array
    {
        return $this->supportedMediaTypes;
    }
}
