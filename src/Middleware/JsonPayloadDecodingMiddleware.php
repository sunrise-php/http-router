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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\InvalidRequestPayloadException;
use JsonException;

/**
 * Import functions
 */
use function is_array;
use function json_decode;
use function sprintf;
use function strpos;
use function strstr;
use function trim;

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
            return $handler->handle(
                $request->withParsedBody(
                    $this->decodeRequestPayload($request)
                )
            );
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
        return $this->getRequestMediaType($request) === 'application/json';
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

        if (false !== strpos($mediaType, ';')) {
            $mediaType = strstr($mediaType, ';', true);
        }

        return trim($mediaType);
    }

    /**
     * Tries to decode the given request's payload
     *
     * @param ServerRequestInterface $request
     *
     * @return array|null
     *
     * @throws InvalidRequestPayloadException
     *         If the request's payload cannot be decoded.
     */
    private function decodeRequestPayload(ServerRequestInterface $request): ?array
    {
        // https://www.php.net/json.constants
        $flags = JSON_BIGINT_AS_STRING | JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR;

        try {
            /** @var mixed */
            $result = json_decode($request->getBody()->__toString(), null, 512, $flags);
        } catch (JsonException $e) {
            throw new InvalidRequestPayloadException(sprintf('Invalid Payload: %s', $e->getMessage()), 0, $e);
        }

        return is_array($result) ? $result : null;
    }
}
