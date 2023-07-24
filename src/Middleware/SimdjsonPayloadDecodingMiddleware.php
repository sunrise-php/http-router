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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\InvalidRequestPayloadException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ServerRequest;
use RuntimeException;

use function extension_loaded;
use function is_array;
use function simdjson_decode;
use function sprintf;

/**
 * Middleware for JSON payload decoding using the Simdjson extension
 *
 * @since 3.0.0
 *
 * @link https://www.php.net/manual/en/book.simdjson.php
 */
final class SimdjsonPayloadDecodingMiddleware implements MiddlewareInterface
{

    /**
     * Constructor of the class
     *
     * @throws LogicException
     *         If the Simdjson extension isn't loaded.
     */
    public function __construct()
    {
        if (!extension_loaded('simdjson')) {
            throw new LogicException(
                'The Simdjson extension is required, run the `pecl install simdjson` command to resolve it.'
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
                $this->decodeJsonPayload(
                    $request->getBody()->__toString()
                )
            );
        }

        return $handler->handle($request);
    }

    /**
     * Tries to decode the given JSON payload
     *
     * @param string $json
     *
     * @return array<array-key, mixed>
     *
     * @throws InvalidRequestPayloadException
     *         If the JSON payload cannot be decoded.
     */
    private function decodeJsonPayload(string $json): array
    {
        try {
            $data = simdjson_decode($json, true, 512);
        } catch (RuntimeException $e) {
            throw new InvalidRequestPayloadException(sprintf('Invalid JSON: %s', $e->getMessage()), 0, $e);
        }

        // According to PSR-7, the data must be an array because
        // we're using the 'associative' option when decoding the JSON.
        if (!is_array($data)) {
            throw new InvalidRequestPayloadException('Unexpected JSON: Expects an array or object.');
        }

        return $data;
    }
}
