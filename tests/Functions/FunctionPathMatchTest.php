<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Functions;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_match;

/**
 * FunctionPathMatchTest
 */
class FunctionPathMatchTest extends TestCase
{

    /**
     * @return void
     */
    public function testPathMatch() : void
    {
        $path = '/foo(/{bar})/{baz<\w+>}(/)';

        $this->assertTrue(path_match($path, '/foo/bar/baz/', $attributes));
        $this->assertSame([
            'bar' => 'bar',
            'baz' => 'baz',
        ], $attributes);

        $this->assertTrue(path_match($path, '/foo/baz', $attributes));
        $this->assertSame([
            'baz' => 'baz',
        ], $attributes);

        $this->assertFalse(path_match($path, '/foo/baz!', $attributes));
        $this->assertSame([], $attributes);
    }
}
