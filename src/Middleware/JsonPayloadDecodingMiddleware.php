<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
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
use Sunrise\Http\Router\Exception\UndecodablePayloadException;

/**
 * Import functions
 */
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function rtrim;
use function strpos;
use function substr;

/**
 * Import constants
 */
use const JSON_BIGINT_AS_STRING;
use const JSON_ERROR_NONE;

/**
 * JsonPayloadDecodingMiddleware
 *
 * @since 2.15.0
 */
class JsonPayloadDecodingMiddleware implements MiddlewareInterface
{

    /**
     * JSON Media Type
     *
     * @var string
     *
     * @link https://datatracker.ietf.org/doc/html/rfc4627
     */
    private const JSON_MEDIA_TYPE = 'application/json';

    /**
     * JSON decoding options
     *
     * @var int
     *
     * @link https://www.php.net/manual/ru/json.constants.php
     */
    protected const JSON_DECODING_OPTIONS = JSON_BIGINT_AS_STRING;

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        if (!$this->isSupportedRequest($request)) {
            return $handler->handle($request);
        }

        $parsedBody = $this->decodeRequestJsonPayload($request);

        return $handler->handle($request->withParsedBody($parsedBody));
    }

    /**
     * Checks if the given request is supported
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    private function isSupportedRequest(ServerRequestInterface $request) : bool
    {
        return self::JSON_MEDIA_TYPE === $this->getRequestMediaType($request);
    }

    /**
     * Gets Media Type from the given request
     *
     * @param ServerRequestInterface $request
     *
     * @return string|null
     *
     * @link https://tools.ietf.org/html/rfc7231#section-3.1.1.1
     */
    private function getRequestMediaType(ServerRequestInterface $request) : ?string
    {
        if (!$request->hasHeader('Content-Type')) {
            return null;
        }

        // type "/" subtype *( OWS ";" OWS parameter )
        $mediaType = $request->getHeaderLine('Content-Type');

        $semicolonPosition = strpos($mediaType, ';');
        if (false === $semicolonPosition) {
            return $mediaType;
        }

        return rtrim(substr($mediaType, 0, $semicolonPosition));
    }

    /**
     * Tries to decode the given request's JSON payload
     *
     * @param ServerRequestInterface $request
     *
     * @return mixed
     *
     * @throws UndecodablePayloadException
     *         If the request's payload cannot be decoded.
     */
    private function decodeRequestJsonPayload(ServerRequestInterface $request)
    {
        json_decode('');
        $result = json_decode($request->getBody()->__toString(), true, 512, static::JSON_DECODING_OPTIONS);
        if (JSON_ERROR_NONE === json_last_error()) {
            return $result;
        }

        throw new UndecodablePayloadException(sprintf('Invalid Payload: %s', json_last_error_msg()));
    }
}
