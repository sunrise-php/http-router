<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\AbstractException;
use Sunrise\Http\Router\Exception\InvalidAnnotationParameterException;

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

        $this->assertInstanceOf(AbstractException::class, $exception);
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
}
