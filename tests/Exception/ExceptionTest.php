<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use Throwable;

/**
 * ExceptionTest
 */
class ExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $exception = new Exception();

        $this->assertInstanceOf(Throwable::class, $exception);
        $this->assertInstanceOf(ExceptionInterface::class, $exception);
    }

    /**
     * @return void
     */
    public function testConstructorWithoutParameters() : void
    {
        $exception = new Exception();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame([], $exception->getContext());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame(null, $exception->getPrevious());
    }

    /**
     * @return void
     */
    public function testMessage() : void
    {
        $message = 'blah';

        $exception = new Exception($message);

        $this->assertSame($message, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testContext() : void
    {
        $context = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $exception = new Exception('blah', $context);

        $this->assertSame($context, $exception->getContext());

        $this->assertSame($context['foo'], $exception->fromContext('foo'));
        $this->assertSame($context['bar'], $exception->fromContext('bar'));

        $this->assertSame(null, $exception->fromContext('baz'));
        $this->assertSame(false, $exception->fromContext('baz', false));
    }

    /**
     * @return void
     */
    public function testCode() : void
    {
        $code = 100;

        $exception = new Exception('blah', [], $code);

        $this->assertSame($code, $exception->getCode());
    }

    /**
     * @return void
     */
    public function testPrevious() : void
    {
        $previous = new Exception();

        $exception = new Exception('blah', [], 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
