<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Middleware;

/**
 * Import classes
 */
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\InvalidPayloadException;

/**
 * Import functions
 */
use function is_array;
use function is_object;
use function json_decode;
use function rtrim;
use function sprintf;
use function strpos;
use function substr;

/**
 * Import constants
 */
use const JSON_BIGINT_AS_STRING;
use const JSON_OBJECT_AS_ARRAY;
use const JSON_THROW_ON_ERROR;

/**
 * JsonPayloadDecodingMiddleware
 *
 * @since 2.15.0
 */
final class JsonPayloadDecodingMiddleware implements MiddlewareInterface
{

    /**
     * JSON media type
     *
     * @link https://datatracker.ietf.org/doc/html/rfc4627
     *
     * @var string
     */
    private const JSON_MEDIA_TYPE = 'application/json';

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isSupportedRequest($request)) {
            $data = $this->decodeRequestJsonPayload($request);
            $request = $request->withParsedBody($data);
        }

        return $handler->handle($request);
    }

    /**
     * Checks if the given request is supported
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    private function isSupportedRequest(ServerRequestInterface $request): bool
    {
        return self::JSON_MEDIA_TYPE === $this->getRequestMediaType($request);
    }

    /**
     * Gets a media type from the given request
     *
     * Returns null if a media type cannot be retrieved.
     *
     * @link https://tools.ietf.org/html/rfc7231#section-3.1.1.1
     *
     * @param ServerRequestInterface $request
     *
     * @return string|null
     */
    private function getRequestMediaType(ServerRequestInterface $request): ?string
    {
        if (!$request->hasHeader('Content-Type')) {
            return null;
        }

        // type "/" subtype *( OWS ";" OWS parameter )
        $mediaType = $request->getHeaderLine('Content-Type');

        $semicolon = strpos($mediaType, ';');
        if (false === $semicolon) {
            return $mediaType;
        }

        return rtrim(substr($mediaType, 0, $semicolon));
    }

    /**
     * Tries to decode the given request's JSON payload
     *
     * @param ServerRequestInterface $request
     *
     * @return array|object|null
     *
     * @throws InvalidPayloadException
     *         If the request's JSON payload cannot be decoded.
     */
    private function decodeRequestJsonPayload(ServerRequestInterface $request)
    {
        // https://www.php.net/json.constants
        $flags = JSON_BIGINT_AS_STRING | JSON_OBJECT_AS_ARRAY;

        try {
            /** @var mixed */
            $result = json_decode($request->getBody()->__toString(), null, 512, $flags | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidPayloadException(sprintf('Invalid Payload: %s', $e->getMessage()), 0, $e);
        }

        if (is_array($result) ||
            is_object($result)) {
            return $result;
        }

        return null;
    }
}
