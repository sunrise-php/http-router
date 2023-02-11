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
 * HTTP Not Acceptable Exception
 *
 * This response is sent when the web server, after performing server-driven content negotiation, doesn't find any
 * content that conforms to the criteria given by the user agent.
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
     * @param array<string> $supported
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(array $supported, ?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message ??= 'Not Acceptable';

        parent::__construct(self::STATUS_NOT_ACCEPTABLE, $message, $code, $previous);

        foreach ($supported as $mediaType) {
            $this->supportedMediaTypes[] = $mediaType;
        }
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
