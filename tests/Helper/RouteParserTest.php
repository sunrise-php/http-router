<?php

namespace Sunrise\Http\Router\Tests\Helper;

use Generator;
use Sunrise\Http\Router\Helper\RouteParser;
use PHPUnit\Framework\TestCase;

class RouteParserTest extends TestCase
{
    /**
     * @dataProvider validRouteDataProvider
     */
    public function testParseValidRoute(string $route, array $expectedVariables): void
    {
        $actualVariables = RouteParser::parseRoute($route);

        $this->assertEquals($expectedVariables, $actualVariables);
    }

    public function validRouteDataProvider(): Generator
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
            '/posts(/{id<\d+>})',
            [

                ['name' => 'id', 'pattern' => '\d+', 'optional' => ['left' => '/', 'right' => ''], 'offset' => 8, 'length' => 9],
            ],
        ];

        yield [
            '(/{lc<[a-z]{2}>})/posts/{id<\d+>}',
            [
                ['name' => 'lc', 'pattern' => '[a-z]{2}', 'optional' => ['left' => '/', 'right' => ''], 'offset' => 2, 'length' => 14],
                ['name' => 'id', 'pattern' => '\d+', 'offset' => 24, 'length' => 9],
            ],
        ];

        yield [
            '/posts(/{id}.json)',
            [
                ['name' => 'id', 'optional' => ['left' => '/', 'right' => '.json'], 'offset' => 8, 'length' => 4],
            ],
        ];
    }
}
