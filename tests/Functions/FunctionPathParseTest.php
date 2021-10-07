<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Functions;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\InvalidPathException;
use Sunrise\Http\Router\Router;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_parse;
use function chr;

/**
 * FunctionPathParseTest
 */
class FunctionPathParseTest extends TestCase
{

    /**
     * @return void
     */
    public function testParsePath() : void
    {
        $path = '/{foo}(/bar/{baz<\w+>}/qux)/quux/{quuux<\d+>}/quuuux(/)';

        $expected = [
            [
                'raw' => '{foo}',
                'withParentheses' => null,
                'name' => 'foo',
                'pattern' => null,
                'isOptional' => false,
                'startPosition' => 1,
                'endPosition' => 5,
            ],
            [
                'raw' => '{baz<\w+>}',
                'withParentheses' => '(/bar/{baz<\w+>}/qux)',
                'name' => 'baz',
                'pattern' => '\w+',
                'isOptional' => true,
                'startPosition' => 12,
                'endPosition' => 21,
            ],
            [
                'raw' => '{quuux<\d+>}',
                'withParentheses' => null,
                'name' => 'quuux',
                'pattern' => '\d+',
                'isOptional' => false,
                'startPosition' => 33,
                'endPosition' => 44,
            ],
        ];

        $this->assertSame($expected, path_parse($path));
    }

    /**
     * @return void
     */
    public function testNamedPatterns() : void
    {
        $path = '/{foo<@slug>}/{bar<@uuid>}';

        $expected = [
            [
                'raw' => '{foo<@slug>}',
                'withParentheses' => null,
                'name' => 'foo',
                'pattern' => Router::$patterns['@slug'],
                'isOptional' => false,
                'startPosition' => 1,
                'endPosition' => 12,
            ],
            [
                'raw' => '{bar<@uuid>}',
                'withParentheses' => null,
                'name' => 'bar',
                'pattern' => Router::$patterns['@uuid'],
                'isOptional' => false,
                'startPosition' => 14,
                'endPosition' => 25,
            ],
        ];

        $this->assertSame($expected, path_parse($path));
    }

    /**
     * @return void
     */
    public function testParenthesesInsideParentheses() : void
    {
        $path = '/test(/{foo}(/{bar}))';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':12] parentheses inside parentheses are not allowed.');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testBracesInsideAttributes() : void
    {
        $path = '/test/{foo{bar}}';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':10] braces inside attributes are not allowed.');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testMultipleAttributesInsideParentheses() : void
    {
        $path = '/test(/{foo}/{bar})';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':13] multiple attributes inside parentheses are not allowed.');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testLessThanCharInsidePatterns() : void
    {
        $path = '/test/{foo<(?<digit>\d+)-\w+>}';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':13] the char "<" inside patterns is not allowed.');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testGreaterThanCharInsidePatterns() : void
    {
        $path = '/test/{foo<[^>]+>}';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':16] at position 16 an extra char ">" was found.');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testEmptyPattern() : void
    {
        $path = '/test/{foo<>}';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':11] an attribute pattern is empty.');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testExtraClosingBrace() : void
    {
        $path = '/test/{foo}/bar}';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':15] at position 15 an extra closing brace was found.');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testEmptyAttributeName() : void
    {
        $path = '/test/{}';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':7] an attribute name is empty.');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testExtraClosingParenthesis() : void
    {
        $path = '/test(/{foo})/bar)';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':17] at position 17 an extra closing parenthesis was found.');

        path_parse($path);
    }

    /**
     * @return void
     *
     * @dataProvider invalidFirstCharForAttributeNameDataProvider
     */
    public function testInvalidFirstCharForAttributeName($char) : void
    {
        $path = '/test/{' . $char . '}';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':7] an attribute name must begin with "A-Za-z_".');

        path_parse($path);
    }

    /**
     * @return void
     *
     * @dataProvider invalidSecondCharForAttributeNameDataProvider
     */
    public function testInvalidSecondCharForAttributeName($char) : void
    {
        $path = '/test/{_' . $char . '}';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':8] an attribute name must contain only "0-9A-Za-z_".');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testNumberSignInPattern() : void
    {
        $path = '/test/{foo<#>}';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . ':11] unallowed character "#" in an attribute pattern.');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testNonClosedParentheses() : void
    {
        $path = '/test(';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . '] the route path contains non-closed parentheses.');

        path_parse($path);
    }

    /**
     * @return void
     */
    public function testNonClosedAttribute() : void
    {
        $path = '/test{';

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('[' . $path . '] the route path contains non-closed attribute.');

        path_parse($path);
    }

    /**
     * @return array
     */
    public function invalidFirstCharForAttributeNameDataProvider() : array
    {
        return [
            [chr(0)], [chr(1)], [chr(2)], [chr(3)], [chr(4)], [chr(5)], [chr(6)], [chr(7)], [chr(8)], [chr(9)],
            [chr(10)], [chr(11)], [chr(12)], [chr(13)], [chr(14)], [chr(15)], [chr(16)], [chr(17)], [chr(18)],
            [chr(19)], [chr(20)], [chr(21)], [chr(22)], [chr(23)], [chr(24)], [chr(25)], [chr(26)], [chr(27)],
            [chr(28)], [chr(29)], [chr(30)], [chr(31)], [chr(32)], [chr(33)], [chr(34)], [chr(35)], [chr(36)],
            [chr(37)], [chr(38)], [chr(39)], [chr(40)], [chr(41)], [chr(42)], [chr(43)], [chr(44)], [chr(45)],
            [chr(46)], [chr(47)], [chr(48)], [chr(49)], [chr(50)], [chr(51)], [chr(52)], [chr(53)], [chr(54)],
            [chr(55)], [chr(56)], [chr(57)], [chr(58)], [chr(59)], [chr(61)], [chr(63)], [chr(64)], [chr(91)],
            [chr(92)], [chr(93)], [chr(94)], [chr(96)], [chr(124)], [chr(126)], [chr(127)],
        ];
    }

    /**
     * @return array
     */
    public function invalidSecondCharForAttributeNameDataProvider() : array
    {
        return [
            [chr(0)], [chr(1)], [chr(2)], [chr(3)], [chr(4)], [chr(5)], [chr(6)], [chr(7)], [chr(8)], [chr(9)],
            [chr(10)], [chr(11)], [chr(12)], [chr(13)], [chr(14)], [chr(15)], [chr(16)], [chr(17)], [chr(18)],
            [chr(19)], [chr(20)], [chr(21)], [chr(22)], [chr(23)], [chr(24)], [chr(25)], [chr(26)], [chr(27)],
            [chr(28)], [chr(29)], [chr(30)], [chr(31)], [chr(32)], [chr(33)], [chr(34)], [chr(35)], [chr(36)],
            [chr(37)], [chr(38)], [chr(39)], [chr(40)], [chr(41)], [chr(42)], [chr(43)], [chr(44)], [chr(45)],
            [chr(46)], [chr(47)], [chr(58)], [chr(59)], [chr(61)], [chr(63)], [chr(64)], [chr(91)], [chr(92)],
            [chr(93)], [chr(94)], [chr(96)], [chr(124)], [chr(126)], [chr(127)],
        ];
    }
}
