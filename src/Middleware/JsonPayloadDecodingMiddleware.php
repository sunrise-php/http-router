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
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->supportsRequest($request)) {
            $data = $this->decodeRequestJsonPayload($request);
            $request = $request->withParsedBody($data);
        }

        return $handler->handle($request);
    }

    /**
     * Checks if the given request is supported
     *
     * @link https://datatracker.ietf.org/doc/html/rfc4627
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    private function supportsRequest(ServerRequestInterface $request): bool
    {
        return 'application/json' === $this->getRequestMediaType($request);
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
        $mediatype = $request->getHeaderLine('Content-Type');

        if ($semicolon = strpos($mediatype, ';')) {
            $mediatype = substr($mediatype, 0, $semicolon);
        }

        return rtrim($mediatype);
    }

    /**
     * Tries to decode the given request's JSON payload
     *
     * @param ServerRequestInterface $request
     *
     * @return array|null
     *
     * @throws InvalidPayloadException
     *         If the request's "JSON" payload cannot be decoded.
     */
    private function decodeRequestJsonPayload(ServerRequestInterface $request): ?array
    {
        // https://www.php.net/json.constants
        $flags = JSON_OBJECT_AS_ARRAY | JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR;

        try {
            /** @var mixed */
            $result = json_decode($request->getBody()->__toString(), null, 512, $flags);
        } catch (JsonException $e) {
            throw new InvalidPayloadException(sprintf('Invalid Payload: %s', $e->getMessage()), 0, $e);
        }

        return is_array($result) ? $result : null;
    }
}
