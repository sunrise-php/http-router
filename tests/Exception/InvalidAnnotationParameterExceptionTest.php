<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\InvalidAnnotationParameterException;
use Sunrise\Http\Router\Exception\InvalidDescriptorArgumentException;

/**
 * InvalidAnnotationParameterExceptionTest
 */
class InvalidAnnotationParameterExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new InvalidAnnotationParameterException();

        $this->assertInstanceOf(Exception::class, $exception);

        // BC for 2.6.0
        $this->assertInstanceOf(InvalidDescriptorArgumentException::class, $exception);
    }

    /**
     * @return void
     */
    public function testMessage() : void
    {
        $message = 'blah';

        $exception = new InvalidAnnotationParameterException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testContext() : void
    {
        $context = ['foo' => 'bar'];

        $exception = new InvalidAnnotationParameterException('blah', $context);

        $this->assertSame($context, $exception->getContext());
    }

    /**
     * @return void
     */
    public function testCode() : void
    {
        $code = 100;

        $exception = new InvalidAnnotationParameterException('blah', [], $code);

        $this->assertSame($code, $exception->getCode());
    }

    /**
     * @return void
     */
    public function testPrevious() : void
    {
        $previous = new \Exception();

        $exception = new InvalidAnnotationParameterException('blah', [], 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
