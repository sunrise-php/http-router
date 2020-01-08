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
    public function testConstructor() : void
    {
        $exception = new BadRequestException();

        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @return void
     */
    public function testMessage() : void
    {
        $message = 'blah';
        $exception = new BadRequestException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testContext() : void
    {
        $context = ['foo' => 'bar'];
        $exception = new BadRequestException('blah', $context);

        $this->assertSame($context, $exception->getContext());
    }

    /**
     * @return void
     */
    public function testCode() : void
    {
        $code = 100;
        $exception = new BadRequestException('blah', [], $code);

        $this->assertSame($code, $exception->getCode());
    }

    /**
     * @return void
     */
    public function testPrevious() : void
    {
        $previous = new \Exception();
        $exception = new BadRequestException('blah', [], 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
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
        $expected = [];
        $exception = new BadRequestException('blah');

        $this->assertSame($expected, $exception->getViolations());
    }
}
