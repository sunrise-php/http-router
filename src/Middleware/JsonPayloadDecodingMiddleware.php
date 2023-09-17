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

namespace Sunrise\Http\Router\Middleware;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SimdJsonException;
use Sunrise\Http\Router\Dictionary\ErrorSource;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Exception\Http\HttpBadRequestException;
use Sunrise\Http\Router\ServerRequest;

use function extension_loaded;
use function is_array;
use function json_decode;
use function simdjson_decode;

use const JSON_THROW_ON_ERROR;

/**
 * JSON payload decoding middleware using the JSON or Simdjson extension
 *
 * @link https://www.php.net/manual/en/intro.json.php
 * @link https://www.php.net/manual/en/intro.simdjson.php
 * @link https://simdjson.org
 *
 * @since 2.15.0
 */
final class JsonPayloadDecodingMiddleware implements MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (ServerRequest::from($request)->clientProducesMediaType(MediaType::json())) {
            $request = $request->withParsedBody($this->decodePayload($request->getBody()->__toString()));
        }

        return $handler->handle($request);
    }

    /**
     * Tries to decode the given JSON payload
     *
     * @param string $payload
     *
     * @return array<array-key, mixed>
     *
     * @throws HttpBadRequestException If the JSON payload couldn't be decoded.
     */
    private function decodePayload(string $payload): array
    {
        if ($payload === '') {
            throw (new HttpBadRequestException('JSON payload cannot be empty.'))
                ->setSource(ErrorSource::CLIENT_REQUEST_BODY);
        }

        try {
            // phpcs:ignore Generic.Files.LineLength
            $data = extension_loaded('simdjson') ? simdjson_decode($payload, true) : json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException|SimdJsonException $e) {
            throw (new HttpBadRequestException('The JSON payload is invalid and couldn‘t be decoded.', previous: $e))
                ->setSource(ErrorSource::CLIENT_REQUEST_BODY);
        }

        if (is_array($data) === false) {
            throw (new HttpBadRequestException('The JSON payload must be in the form of an array or an object.'))
                ->setSource(ErrorSource::CLIENT_REQUEST_BODY);
        }

        return $data;
    }
}
