<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Helper;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Helper\RouteParser;

final class RouteParserTest extends TestCase
{
    #[DataProvider('validRouteProvider')]
    public function testParseValidRoute(string $route, array $expectedVariables): void
    {
        $actualVariables = RouteParser::parseRoute($route);
        self::assertEquals($expectedVariables, $actualVariables);
    }

    #[DataProvider('invalidRouteProvider')]
    public function testParseInvalidRoute(string $route, string $expectedMessageRegex): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches($expectedMessageRegex);
        RouteParser::parseRoute($route);
    }

    public static function validRouteProvider(): Generator
    {
        yield [
            '',
            [],
        ];

        yield [
            '/',
            [],
        ];

        yield [
            '/posts/{id}',
            [
                ['statement' => '{id}', 'name' => 'id'],
            ],
        ];

        yield [
            '/posts/{id<\d+>}',
            [
                ['statement' => '{id<\d+>}', 'name' => 'id', 'pattern' => '\d+'],
            ],
        ];

        yield [
            '/posts(/{id})',
            [
                ['statement' => '{id}', 'name' => 'id', 'optional_part' => '(/{id})'],
            ],
        ];

        yield [
            '/posts(/{id<\d+>})',
            [

                ['statement' => '{id<\d+>}', 'name' => 'id', 'pattern' => '\d+', 'optional_part' => '(/{id<\d+>})'],
            ],
        ];

        yield [
            '/posts(/{id<\d+>}.json)',
            [
                ['statement' => '{id<\d+>}', 'name' => 'id', 'pattern' => '\d+', 'optional_part' => '(/{id<\d+>}.json)'],
            ],
        ];

        yield [
            '/posts/{id<\d+>}(.json)',
            [
                ['statement' => '{id<\d+>}', 'name' => 'id', 'pattern' => '\d+'],
            ],
        ];

        yield [
            '(/{lang<[a-z]{2}>})/posts(/{id<\d+>})',
            [
                ['statement' => '{lang<[a-z]{2}>}', 'name' => 'lang', 'pattern' => '[a-z]{2}', 'optional_part' => '(/{lang<[a-z]{2}>})'],
                ['statement' => '{id<\d+>}', 'name' => 'id', 'pattern' => '\d+', 'optional_part' => '(/{id<\d+>})'],
            ],
        ];
    }

    public static function invalidRouteProvider(): Generator
    {
        yield [
            '((',
            '/nested optional parts are not supported/',
        ];

        yield [
            ')',
            '/open optional part was not found/',
        ];

        yield [
            '{{',
            '/nested variables are not supported/',
        ];

        yield [
            '({x}{',
            '/more than one variable inside an optional part is not supported/',
        ];

        yield [
            '}',
            '/open variable was not found/',
        ];

        yield [
            '{}',
            '/name is required for its declaration/',
        ];

        yield [
            '{<<',
            '/nested patterns are not supported/',
        ];

        yield [
            '{<.><',
            '/pattern must be preceded by the variable name/',
        ];

        yield [
            '{>',
            '/open pattern was not found/',
        ];

        yield [
            '{<>',
            '/content is required for its declaration/',
        ];

        yield [
            '{0',
            '/variable names cannot start with digits/',
        ];

        yield [
            '{!',
            '/variable names must consist only of digits, letters and underscores/'
        ];

        yield [
            '{xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            '/variable names must not exceed 32 characters/'
        ];

        yield [
            '/{<#/',
            '/variable patterns cannot contain the character "#"/'
        ];

        yield [
            '{x<.>!}',
            '/variable at this position must be closed/'
        ];

        yield [
            '(',
            '/contains an unclosed variable or optional part/'
        ];

        yield [
            '{',
            '/contains an unclosed variable or optional part/'
        ];
    }
}
