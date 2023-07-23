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

use function join;

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
     * Supported media types
     *
     * @var list<string>
     */
    private array $supportedMediaTypes = [];

    /**
     * Constructor of the class
     *
     * @param list<string> $supportedMediaTypes
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        array $supportedMediaTypes,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message ??= 'Unsupported Media Type';

        parent::__construct(self::STATUS_UNSUPPORTED_MEDIA_TYPE, $message, $code, $previous);

        $this->supportedMediaTypes = $supportedMediaTypes;

        $this->addHeaderField('Accept', $this->getJoinedSupportedTypes());
    }

    /**
     * Gets supported media types
     *
     * @return list<string>
     */
    final public function getSupportedTypes(): array
    {
        return $this->supportedMediaTypes;
    }

    /**
     * Gets joined supported media types
     *
     * @return string
     */
    final public function getJoinedSupportedTypes(): string
    {
        return join(',', $this->supportedMediaTypes);
    }
}
