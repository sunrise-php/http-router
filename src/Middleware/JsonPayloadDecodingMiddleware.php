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
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ServerRequest;

use function extension_loaded;
use function is_array;
use function json_decode;
use function sprintf;

use const JSON_BIGINT_AS_STRING;
use const JSON_THROW_ON_ERROR;

/**
 * Middleware for JSON payload decoding using the JSON extension
 *
 * @since 2.15.0
 *
 * @link https://www.php.net/manual/en/book.json.php
 */
final class JsonPayloadDecodingMiddleware implements MiddlewareInterface
{

    /**
     * Constructor of the class
     *
     * @throws LogicException If the JSON extension isn't loaded.
     */
    public function __construct()
    {
        if (!extension_loaded('json')) {
            throw new LogicException(
                'The JSON extension is required, run the `pecl install json` command to resolve it.'
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (ServerRequest::from($request)->isJson()) {
            $request = $request->withParsedBody(
                $this->decodePayload(
                    $request->getBody()->__toString()
                )
            );
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
     * @throws InvalidRequestPayloadException If the JSON payload cannot be decoded.
     */
    private function decodePayload(string $payload): array
    {
        try {
            $data = json_decode($payload, true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidRequestPayloadException(sprintf('Invalid JSON: %s', $e->getMessage()), 0, $e);
        }

        // According to PSR-7, the data must be an array
        // because we're using the 'associative' option when decoding the JSON.
        if (!is_array($data)) {
            throw new InvalidRequestPayloadException('Unexpected JSON: Expects an array or object.');
        }

        return $data;
    }
}
