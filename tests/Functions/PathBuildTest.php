<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\InvalidAttributeValueException;
use Sunrise\Http\Router\Exception\MissingAttributeValueException;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_build;

/**
 * PathBuildTest
 */
class PathBuildTest extends TestCase
{

    /**
     * @return void
     */
    public function testBuildPath() : void
    {
        $path = '/foo(/bar/{baz}/qux)/quux(/quuux/{quuuux<\w+>}/quuuuux)/{quuuuuux}';
        $expected = '/foo/quux/quuux/quuuux/quuuuux/quuuuuux';

        $this->assertSame($expected, path_build($path, [
            'quuuux' => 'quuuux',
            'quuuuuux' => 'quuuuuux',
        ]));
    }

    /**
     * @return void
     */
    public function testBuildPathWithoutRequiredAttributeValue() : void
    {
        $path = '/foo/{bar}/{baz}';

        $this->expectException(MissingAttributeValueException::class);
        $this->expectExceptionMessage(
            '[' . $path . '] build error: no value given for the attribute "baz".'
        );

        path_build($path, [
            'bar' => 'bar',
        ]);
    }

    /**
     * @return void
     */
    public function testBuildPathWithInvalidAttributeValue() : void
    {
        $path = '/foo/{bar}/{baz<[a-z]+>}';

        $this->expectException(InvalidAttributeValueException::class);
        $this->expectExceptionMessage(
            '[' . $path . '] build error: the given value for the attribute "baz" does not match its pattern.'
        );

        path_build($path, [
            'bar' => 'bar',
            'baz' => '42',
        ], true);
    }
}
