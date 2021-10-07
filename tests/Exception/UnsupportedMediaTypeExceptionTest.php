<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\UnsupportedMediaTypeException;

/**
 * Import functions
 */
use function implode;

/**
 * UnsupportedMediaTypeExceptionTest
 */
class UnsupportedMediaTypeExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $exception = new UnsupportedMediaTypeException();

        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @return void
     */
    public function testType() : void
    {
        $expected = 'application/octet-stream';

        $exception = new UnsupportedMediaTypeException('blah', [
            'type' => $expected,
        ]);

        $this->assertSame($expected, $exception->getType());
    }

    /**
     * @return void
     */
    public function testTypeWithEmptyContext() : void
    {
        $exception = new UnsupportedMediaTypeException('blah');

        $this->assertSame('', $exception->getType());
    }

    /**
     * @return void
     */
    public function testSupportedTypes() : void
    {
        $expected = [
            'application/json',
            'application/x-www-form-urlencoded',
        ];

        $exception = new UnsupportedMediaTypeException('blah', [
            'supported' => $expected,
        ]);

        $this->assertSame($expected, $exception->getSupportedTypes());
    }

    /**
     * @return void
     */
    public function testSupportedTypesWithEmptyContext() : void
    {
        $exception = new UnsupportedMediaTypeException('blah');

        $this->assertSame([], $exception->getSupportedTypes());
    }

    /**
     * @return void
     */
    public function testGluingSupportedTypes() : void
    {
        $types = ['foo', 'bar'];

        $expected = implode(',', $types);

        $exception = new UnsupportedMediaTypeException('blah', [
            'supported' => $types,
        ]);

        $this->assertSame($expected, $exception->getJoinedSupportedTypes());
    }

    /**
     * @return void
     */
    public function testGluingSupportedTypesWithEmptyContext() : void
    {
        $exception = new UnsupportedMediaTypeException('blah');

        $this->assertSame('', $exception->getJoinedSupportedTypes());
    }
}
