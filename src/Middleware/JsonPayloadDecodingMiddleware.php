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
use Sunrise\Http\Router\Entity\MediaType\ServerMediaTypeFactory;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\ServerRequest;

use function is_array;
use function json_decode;

use const JSON_BIGINT_AS_STRING;
use const JSON_THROW_ON_ERROR;

/**
 * JSON payload decoding middleware
 *
 * @since 2.15.0
 */
final class JsonPayloadDecodingMiddleware implements MiddlewareInterface
{
    public const DEFAULT_DECODING_FLAGS = JSON_BIGINT_AS_STRING;
    public const DEFAULT_DECODING_DEPTH = 512;

    /**
     * @since 3.0.0
     */
    public function __construct(
        private readonly ?int $decodingFlags = null,
        private readonly ?int $decodingDepth = null,
        private readonly ?int $errorStatusCode = null,
        private readonly ?string $errorMessage = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws HttpExceptionInterface If the request's payload couldn't be decoded.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (ServerRequest::create($request)->clientProducesMediaType(ServerMediaTypeFactory::json())) {
            $request = $request->withParsedBody($this->decodePayload((string) $request->getBody()));
        }

        return $handler->handle($request);
    }

    /**
     * @return array<array-key, mixed>
     *
     * @throws HttpExceptionInterface If the payload couldn't be decoded.
     */
    private function decodePayload(string $payload): array
    {
        if ($payload === '') {
            throw HttpExceptionFactory::emptyJsonPayload($this->errorMessage, $this->errorStatusCode);
        }

        $decodingFlags = $this->decodingFlags ?? self::DEFAULT_DECODING_FLAGS;
        /** @psalm-var int<1, 2147483647> $decodingDepth */
        $decodingDepth = $this->decodingDepth ?? self::DEFAULT_DECODING_DEPTH;

        try {
            $data = json_decode($payload, true, $decodingDepth, $decodingFlags | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw HttpExceptionFactory::invalidJsonPayload($this->errorMessage, $this->errorStatusCode, previous: $e);
        }

        if (!is_array($data)) {
            throw HttpExceptionFactory::invalidJsonPayloadFormat($this->errorMessage, $this->errorStatusCode);
        }

        return $data;
    }
}
