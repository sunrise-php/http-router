<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Codec;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Sunrise\Http\Router\Codec\JsonCodec;
use PHPUnit\Framework\TestCase;

use const JSON_HEX_AMP;
use const JSON_INVALID_UTF8_SUBSTITUTE;

final class JsonCodecTest extends TestCase
{
    public function testGetSupportedMediaTypes(): void
    {
        $supportedMediaTypeIdentifiers = [];
        foreach ((new JsonCodec())->getSupportedMediaTypes() as $supportedMediaType) {
            $supportedMediaTypeIdentifiers[] = $supportedMediaType->getIdentifier();
        }

        $this->assertSame(['application/json'], $supportedMediaTypeIdentifiers);
    }

    #[DataProvider('decodeDataProvider')]
    public function testDecode(mixed $decodedData, string $decodingData, array $decodingContext = [], array $codecContext = []): void
    {
        $this->assertSame($decodedData, (new JsonCodec($codecContext))->decode($decodingData, $decodingContext));
    }

    #[DataProvider('encodeDataProvider')]
    public function testEncode(string $encodedData, mixed $encodingData, array $encodingContext = [], array $codecContext = []): void
    {
        $this->assertSame($encodedData, (new JsonCodec($codecContext))->encode($encodingData, $encodingContext));
    }

    public static function decodeDataProvider(): Generator
    {
        yield [
            ['foo' => 'bar'],
            '{"foo":"bar"}',
        ];

        yield [
            ['foo' => '9223372036854775808'],
            '{"foo":9223372036854775808}',
        ];

        yield [
            ['foo' => "\xef\xbf\xbd"],
            '{"foo":"' . "\xff" . '"}',
            [JsonCodec::CONTEXT_KEY_DECODING_FLAGS => JSON_INVALID_UTF8_SUBSTITUTE],
        ];

        yield [
            ['foo' => "\xef\xbf\xbd"],
            '{"foo":"' . "\xff" . '"}',
            [],
            [JsonCodec::CONTEXT_KEY_DECODING_FLAGS => JSON_INVALID_UTF8_SUBSTITUTE],
        ];
    }

    public static function encodeDataProvider(): Generator
    {
        yield [
            '{"foo":"bar"}',
            ['foo' => 'bar'],
        ];

        yield [
            '{"foo":"bar \u0026 baz"}',
            ['foo' => 'bar & baz'],
            [JsonCodec::CONTEXT_KEY_ENCODING_FLAGS => JSON_HEX_AMP],
        ];

        yield [
            '{"foo":"bar \u0026 baz"}',
            ['foo' => 'bar & baz'],
            [],
            [JsonCodec::CONTEXT_KEY_ENCODING_FLAGS => JSON_HEX_AMP],
        ];
    }
}
