<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\BadRequestException;

/**
 * BadRequestExceptionTest
 */
class BadRequestExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $exception = new BadRequestException();

        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @return void
     */
    public function testErrors() : void
    {
        $expected = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $exception = new BadRequestException('blah', [
            'errors' => $expected,
        ]);

        $this->assertSame($expected, $exception->getErrors());
    }

    /**
     * @return void
     */
    public function testErrorsWithEmptyContext() : void
    {
        $exception = new BadRequestException('blah');

        $this->assertSame([], $exception->getErrors());
    }

    /**
     * @return void
     */
    public function testViolations() : void
    {
        $expected = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $exception = new BadRequestException('blah', [
            'violations' => $expected,
        ]);

        $this->assertSame($expected, $exception->getViolations());
    }

    /**
     * @return void
     */
    public function testViolationsWithEmptyContext() : void
    {
        $exception = new BadRequestException('blah');

        $this->assertSame([], $exception->getViolations());
    }
}
