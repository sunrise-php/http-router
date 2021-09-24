<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Functions;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_regex;

/**
 * FunctionPathRegexTest
 */
class FunctionPathRegexTest extends TestCase
{

    /**
     * @return void
     */
    public function testBuildPathRegex() : void
    {
        $path = '/foo(/{bar})/{baz<\w+>}(/)';
        $expected = '#^/foo(?:/(?<bar>[^/]+))?/(?<baz>\w+)(?:/)?$#uD';

        $this->assertSame($expected, path_regex($path));
    }
}
