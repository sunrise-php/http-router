<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Functions;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_plain;

/**
 * FunctionPathPlainTest
 */
class FunctionPathPlainTest extends TestCase
{

    /**
     * @return void
     */
    public function testPlainPath() : void
    {
        $path = '/foo(/{bar})/{baz<\w+>}(/)';
        $expected = '/foo/{bar}/{baz}/';

        $this->assertSame($expected, path_plain($path));
    }
}
