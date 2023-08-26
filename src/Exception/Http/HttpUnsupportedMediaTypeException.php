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

use Sunrise\Http\Router\Entity\MediaType;
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
     * Constructor of the class
     *
     * @param list<MediaType> $supportedMediaTypes
     * @param non-empty-string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    // phpcs:ignore Generic.Files.LineLength
    public function __construct(private array $supportedMediaTypes, ?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message ??= 'The request couldn‘t be processed due to an unsupported format of the request payload.';

        parent::__construct(self::STATUS_UNSUPPORTED_MEDIA_TYPE, $message, $code, $previous);

        $this->setReasonPhrase('Unsupported Media Type');

        $this->addHeader('Accept', ...$supportedMediaTypes);
    }

    /**
     * @return list<MediaType>
     */
    public function getSupportedMediaTypes(): array
    {
        return $this->supportedMediaTypes;
    }
}
