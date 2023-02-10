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
 * Import functions
 */
use function join;

/**
 * HTTP Unsupported Media Type Exception
 *
 * The media format of the requested data is not supported by the server, so the server is rejecting the request.
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
     * @param string[] $supportedMediaTypes
     * @param ?string $message
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(
        array $supportedMediaTypes,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message ??= 'Unsupported Media Type';

        parent::__construct(self::STATUS_UNSUPPORTED_MEDIA_TYPE, $message, $code, $previous);

        foreach ($supportedMediaTypes as $supportedMediaType) {
            $this->supportedMediaTypes[] = $supportedMediaType;
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

    /**
     * Gets joined supported media types
     *
     * @return string
     */
    final public function getJoinedSupportedTypes(): string
    {
        return join(',', $this->getSupportedTypes());
    }

    /**
     * Gets arguments for an Accept header field
     *
     * Returns an array where key 0 contains the header name and key 1 contains its value.
     *
     * <code>
     *   $response = $response
     *       ->withStatus($e->getStatusCode())
     *       ->withHeader(...$e->getAcceptHeaderArguments());
     * </code>
     *
     * @return array{0: string, 1: string}
     */
    final public function getAcceptHeaderArguments(): array
    {
        return ['Accept', $this->getJoinedSupportedTypes()];
    }
}
