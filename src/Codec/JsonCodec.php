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

namespace Sunrise\Http\Router\Codec;

use JsonException;
use Sunrise\Http\Router\CodecInterface;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\Exception\CodecException;

use function json_decode;
use function json_encode;

use const JSON_BIGINT_AS_STRING;
use const JSON_OBJECT_AS_ARRAY;
use const JSON_THROW_ON_ERROR;

/**
 * @since 3.0.0
 */
final class JsonCodec implements CodecInterface
{
    public const CONTEXT_KEY_DECODING_FLAGS = 'decoding_flags';
    public const CONTEXT_KEY_DECODING_MAX_DEPTH = 'decoding_max_depth';
    public const CONTEXT_KEY_ENCODING_FLAGS = 'encoding_flags';
    public const CONTEXT_KEY_ENCODING_MAX_DEPTH = 'encoding_max_depth';

    private const DEFAULT_CODING_MAX_DEPTH = 512;

    /**
     * @param array<array-key, mixed> $context
     */
    public function __construct(
        private readonly array $context = [],
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getSupportedMediaTypes(): array
    {
        return [MediaType::JSON];
    }

    /**
     * @inheritDoc
     */
    public function decode(string $data, array $context = []): mixed
    {
        $context += $this->context;

        /** @var int $decodingFlags */
        $decodingFlags = $context[self::CONTEXT_KEY_DECODING_FLAGS] ?? 0;
        /** @var int<1, 2147483647> $decodingMaxDepth */
        $decodingMaxDepth = $context[self::CONTEXT_KEY_DECODING_MAX_DEPTH] ?? self::DEFAULT_CODING_MAX_DEPTH;

        $decodingFlags |= JSON_OBJECT_AS_ARRAY | JSON_BIGINT_AS_STRING;

        try {
            return json_decode($data, null, $decodingMaxDepth, $decodingFlags | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new CodecException($e->getMessage(), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function encode(mixed $data, array $context = []): string
    {
        $context += $this->context;

        /** @var int $encodingFlags */
        $encodingFlags = $context[self::CONTEXT_KEY_ENCODING_FLAGS] ?? 0;
        /** @var int<1, 2147483647> $encodingMaxDepth */
        $encodingMaxDepth = $context[self::CONTEXT_KEY_ENCODING_MAX_DEPTH] ?? self::DEFAULT_CODING_MAX_DEPTH;

        try {
            return json_encode($data, $encodingFlags | JSON_THROW_ON_ERROR, $encodingMaxDepth);
        } catch (JsonException $e) {
            throw new CodecException($e->getMessage(), previous: $e);
        }
    }
}
