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
 * HTTP Not Acceptable Exception
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/406
 *
 * @since 3.0.0
 */
class HttpNotAcceptableException extends HttpException
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
        $message ??= 'Not Acceptable';

        parent::__construct(self::STATUS_NOT_ACCEPTABLE, $message, $code, $previous);

        $this->supportedMediaTypes = $supportedMediaTypes;
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
}
