<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\ExceptionInterface;
use Sunrise\Http\Router\Exception\AbstractException;
use RuntimeException;

/**
 * AbstractExceptionTest
 */
class AbstractExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new class extends AbstractException {
        };

        $this->assertInstanceOf(ExceptionInterface::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
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

        $exception = new class('blah', $context) extends AbstractException {
        };

        $this->assertSame($context, $exception->getContext());
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

        $exception = new class('blah', $context) extends AbstractException {
        };

        $this->assertSame($context['foo'], $exception->fromContext('foo'));
        $this->assertSame($context['bar'], $exception->fromContext('bar'));

        // undefined the context keys...
        $this->assertSame(null, $exception->fromContext('baz'));
        $this->assertSame(false, $exception->fromContext('baz', false));
    }
}
