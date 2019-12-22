<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\MiddlewareAlreadyExistsException;

/**
 * MiddlewareAlreadyExistsExceptionTest
 */
class MiddlewareAlreadyExistsExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new MiddlewareAlreadyExistsException();

        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @return void
     */
    public function testMessage() : void
    {
        $message = 'blah';

        $exception = new MiddlewareAlreadyExistsException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testContext() : void
    {
        $context = ['foo' => 'bar'];

        $exception = new MiddlewareAlreadyExistsException('blah', $context);

        $this->assertSame($context, $exception->getContext());
    }

    /**
     * @return void
     */
    public function testCode() : void
    {
        $code = 100;

        $exception = new MiddlewareAlreadyExistsException('blah', [], $code);

        $this->assertSame($code, $exception->getCode());
    }

    /**
     * @return void
     */
    public function testPrevious() : void
    {
        $previous = new \Exception();

        $exception = new MiddlewareAlreadyExistsException('blah', [], 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
