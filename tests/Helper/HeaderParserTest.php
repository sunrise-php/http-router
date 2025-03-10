<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Helper;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Helper\HeaderParser;

use function str_repeat;

final class HeaderParserTest extends TestCase
{
    #[DataProvider('parseHeaderDataProvider')]
    public function testParseHeader(string $header, array $expectedValues): void
    {
        $actualValues = HeaderParser::parseHeader($header);
        self::assertSame($expectedValues, $actualValues);
    }

    public static function parseHeaderDataProvider(): Generator
    {
        yield ['', []];
        yield ['foo', [['foo', []]]];
        yield ['foo, bar', [['foo', []], ['bar', []]]];
        yield ['foo; a=b, bar; b=c', [['foo', ['a' => 'b']], ['bar', ['b' => 'c']]]];
        yield ['foo; a="b", bar; b="c"', [['foo', ['a' => 'b']], ['bar', ['b' => 'c']]]];
        yield ['foo; a="\"b\"", bar; b="\"c\""', [['foo', ['a' => '"b"']], ['bar', ['b' => '"c"']]]];
        yield [' foo ; a = "\"b\"" , bar ; b = "\"c\"" ', [['foo', ['a' => '"b"']], ['bar', ['b' => '"c"']]]];
        yield ['foo;a="\"b\"",bar;b="\"c\""', [['foo', ['a' => '"b"']], ['bar', ['b' => '"c"']]]];
        yield ['foo; a=" ", bar; b=" "', [['foo', ['a' => '']], ['bar', ['b' => '']]]];
        yield [' ; = " \" \" " , foo ; = , bar ; = b, baz; c=d, qux; e=f; g=h', [1 => ['foo', []], ['bar', []], ['baz', ['c' => 'd']], ['qux', ['e' => 'f', 'g' => 'h']]]];
        yield ["\0foo\0", [['foo', []]]]; // ignore invalid chars
        yield [str_repeat('0', 513), [[str_repeat('0', 512), []]]]; // max length
    }
}
