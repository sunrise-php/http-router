<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use RuntimeException;

/**
 * ExceptionTest
 */
class ExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new Exception();

        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
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
        $previous = new \Exception();

        $exception = new Exception('blah', [], 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * @return void
     */
    public function testFromContext() : void
    {
        $context = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $exception = new Exception('blah', $context);

        $this->assertSame($context['foo'], $exception->fromContext('foo'));
        $this->assertSame($context['bar'], $exception->fromContext('bar'));

        // undefined the context keys...
        $this->assertSame(null, $exception->fromContext('baz'));
        $this->assertSame(false, $exception->fromContext('baz', false));
    }
}
