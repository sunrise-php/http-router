<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Codec;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Sunrise\Http\Router\Codec\JsonCodec;
use Sunrise\Http\Router\Exception\CodecException;
use PHPUnit\Framework\TestCase;

use const JSON_INVALID_UTF8_SUBSTITUTE;

final class JsonCodecTest extends TestCase
{
    public function testSupportedMediaTypes(): void
    {
        $supportedMediaTypeIdentifiers = [];
        foreach ((new JsonCodec())->getSupportedMediaTypes() as $supportedMediaType) {
            $supportedMediaTypeIdentifiers[] = $supportedMediaType->getIdentifier();
        }

        $this->assertSame(['application/json'], $supportedMediaTypeIdentifiers);
    }

    #[DataProvider('decodeDataProvider')]
    public function testDecode(mixed $expectedData, string $codingData, array $codingContext = [], array $codecContext = []): void
    {
        $this->assertSame($expectedData, (new JsonCodec($codecContext))->decode($codingData, $codingContext));
    }

    #[DataProvider('encodeDataProvider')]
    public function testEncode(string $expectedData, mixed $codingData, array $codingContext = [], array $codecContext = []): void
    {
        $this->assertSame($expectedData, (new JsonCodec($codecContext))->encode($codingData, $codingContext));
    }

    #[DataProvider('invalidDecodableDataProvider')]
    public function testDecodeInvalidData(string $expectedMessage, string $codingData, array $codingContext = [], array $codecContext = []): void
    {
        $this->expectException(CodecException::class);
        $this->expectExceptionMessageMatches($expectedMessage);
        (new JsonCodec($codecContext))->decode($codingData, $codingContext);
    }

    #[DataProvider('invalidEncodableDataProvider')]
    public function testEncodeInvalidData(string $expectedMessage, mixed $codingData, array $codingContext = [], array $codecContext = []): void
    {
        $this->expectException(CodecException::class);
        $this->expectExceptionMessageMatches($expectedMessage);
        (new JsonCodec($codecContext))->encode($codingData, $codingContext);
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
            '{"foo":"\ufffd"}',
            ['foo' => "\xff"],
            [JsonCodec::CONTEXT_KEY_ENCODING_FLAGS => JSON_INVALID_UTF8_SUBSTITUTE],
        ];

        yield [
            '{"foo":"\ufffd"}',
            ['foo' => "\xff"],
            [],
            [JsonCodec::CONTEXT_KEY_ENCODING_FLAGS => JSON_INVALID_UTF8_SUBSTITUTE],
        ];
    }

    public static function invalidDecodableDataProvider(): Generator
    {
        yield [
            '/Syntax error/',
            '',
        ];

        yield [
            '/Syntax error/',
            '!',
        ];

        yield [
            '/Maximum stack depth exceeded/',
            '[]',
            [JsonCodec::CONTEXT_KEY_DECODING_MAX_DEPTH => 1],
        ];

        yield [
            '/Maximum stack depth exceeded/',
            '[]',
            [],
            [JsonCodec::CONTEXT_KEY_DECODING_MAX_DEPTH => 1],
        ];
    }

    public static function invalidEncodableDataProvider(): Generator
    {
        yield [
            '/Malformed UTF-8 characters/',
            "\xff",
        ];

        yield [
            '/Maximum stack depth exceeded/',
            [[]],
            [JsonCodec::CONTEXT_KEY_ENCODING_MAX_DEPTH => 1],
        ];

        yield [
            '/Maximum stack depth exceeded/',
            [[]],
            [],
            [JsonCodec::CONTEXT_KEY_ENCODING_MAX_DEPTH => 1],
        ];
    }
}
