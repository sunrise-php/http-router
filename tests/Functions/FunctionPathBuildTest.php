<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Functions;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\InvalidAttributeValueException;
use Sunrise\Http\Router\Exception\MissingAttributeValueException;

/**
 * Import functions
 */
use function array_keys;
use function Sunrise\Http\Router\path_build;

/**
 * FunctionPathBuildTest
 */
class FunctionPathBuildTest extends TestCase
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
    public function testBuildPathWithoutRequiredAttribute() : void
    {
        $path = '/foo/{bar}/{baz}';

        $this->expectException(MissingAttributeValueException::class);
        $this->expectExceptionMessage(
            '[' . $path . '] build error: no value given for the attribute "baz".'
        );

        try {
            path_build($path, [
                'bar' => 'bar',
            ]);
        } catch (MissingAttributeValueException $e) {
            $this->assertSame(['path', 'match'], array_keys($e->getContext()));
            $this->assertSame($path, $e->fromContext('path'));

            throw $e;
        }
    }

    /**
     * @return void
     */
    public function testBuildPathWithInvalidAttribute() : void
    {
        $path = '/foo/{bar}/{baz<[a-z]+>}';

        $this->expectException(InvalidAttributeValueException::class);
        $this->expectExceptionMessage(
            '[' . $path . '] build error: the given value for the attribute "baz" does not match its pattern.'
        );

        try {
            path_build($path, [
                'bar' => 'bar',
                'baz' => '42',
            ], true);
        } catch (InvalidAttributeValueException $e) {
            $this->assertSame(['path', 'value', 'match'], array_keys($e->getContext()));
            $this->assertSame($path, $e->fromContext('path'));
            $this->assertSame('42', $e->fromContext('value'));

            throw $e;
        }
    }
}
