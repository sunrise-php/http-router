<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Helper;

use Generator;
use Sunrise\Http\Router\Exception\InvalidRouteParsingSubjectException;
use Sunrise\Http\Router\Helper\RouteParser;
use PHPUnit\Framework\TestCase;

final class RouteParserTest extends TestCase
{
    /**
     * @dataProvider validRouteDataProvider
     */
    public function testParseValidRoute(string $route, array $expectedVariables): void
    {
        $actualVariables = RouteParser::parseRoute($route);

        $this->assertEquals($expectedVariables, $actualVariables);
    }

    /**
     * @dataProvider invalidRouteDataProvider
     */
    public function testParseInvalidRoute(string $route, string $expectedMessageRegex): void
    {
        $this->expectException(InvalidRouteParsingSubjectException::class);
        $this->expectExceptionMessageMatches($expectedMessageRegex);

        RouteParser::parseRoute($route);
    }

    private function validRouteDataProvider(): Generator
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
                ['name' => 'id', 'offset' => 7, 'length' => 4],
            ],
        ];

        yield [
            '/posts/{id<\d+>}',
            [
                ['name' => 'id', 'pattern' => '\d+', 'offset' => 7, 'length' => 9],
            ],
        ];

        yield [
            '/posts(/{id})',
            [
                ['name' => 'id', 'optional' => ['left' => '/', 'right' => ''], 'offset' => 8, 'length' => 4],
            ],
        ];

        yield [
            '/posts(/{id<\d+>})',
            [

                ['name' => 'id', 'pattern' => '\d+', 'optional' => ['left' => '/', 'right' => ''], 'offset' => 8, 'length' => 9],
            ],
        ];

        yield [
            '/posts(/{id<\d+>}.json)',
            [
                ['name' => 'id', 'pattern' => '\d+', 'optional' => ['left' => '/', 'right' => '.json'], 'offset' => 8, 'length' => 9],
            ],
        ];

        yield [
            '/posts/{id<\d+>}(.json)',
            [
                ['name' => 'id', 'pattern' => '\d+', 'offset' => 7, 'length' => 9],
            ],
        ];

        yield [
            '(/{lang<[a-z]{2}>})/posts(/{id<\d+>})',
            [
                ['name' => 'lang', 'pattern' => '[a-z]{2}', 'optional' => ['left' => '/', 'right' => ''], 'offset' => 2, 'length' => 16],
                ['name' => 'id', 'pattern' => '\d+', 'optional' => ['left' => '/', 'right' => ''], 'offset' => 27, 'length' => 9],
            ],
        ];
    }

    private function invalidRouteDataProvider(): Generator
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
            '{aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            '/variable names must not exceed 32 characters/'
        ];

        yield [
            '/{<#/',
            '/variable patterns cannot contain the character #/'
        ];

        yield [
            '{x<.>!}',
            '/variable at this position must be closed/'
        ];

        yield [
            '(',
            '/contains an unclosed optional part or variable/'
        ];

        yield [
            '{',
            '/contains an unclosed optional part or variable/'
        ];
    }
}
