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
use Sunrise\Http\Router\Exception\InvalidRequestPayloadException;
use Sunrise\Http\Router\ServerRequest;

use function is_array;
use function json_decode;
use function sprintf;

use const JSON_BIGINT_AS_STRING;
use const JSON_THROW_ON_ERROR;

/**
 * Middleware for JSON payload decoding
 *
 * @since 2.15.0
 *
 * @link https://www.php.net/manual/en/book.json.php
 */
final class JsonPayloadDecodingMiddleware implements MiddlewareInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (ServerRequest::from($request)->isJson()) {
            $request = $request->withParsedBody(
                $this->decodeRequestPayload($request)
            );
        }

        return $handler->handle($request);
    }

    /**
     * Tries to decode the given request's payload
     *
     * @param ServerRequestInterface $request
     *
     * @return array<array-key, mixed>
     *
     * @throws InvalidRequestPayloadException
     *         If the request's payload cannot be decoded.
     */
    private function decodeRequestPayload(ServerRequestInterface $request): array
    {
        $json = $request->getBody()->__toString();

        try {
            $data = json_decode($json, true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidRequestPayloadException(sprintf('Invalid Payload: %s', $e->getMessage()), 0, $e);
        }

        if (!is_array($data)) {
            throw new InvalidRequestPayloadException('Unexpected JSON: Expects an array or object.');
        }

        return $data;
    }
}
