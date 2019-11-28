<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;

/**
 * MethodNotAllowedExceptionTest
 */
class MethodNotAllowedExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new MethodNotAllowedException();

        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @return void
     */
    public function testMessage() : void
    {
        $message = 'blah';

        $exception = new MethodNotAllowedException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testContext() : void
    {
        $context = ['foo' => 'bar'];

        $exception = new MethodNotAllowedException('blah', $context);

        $this->assertSame($context, $exception->getContext());
    }

    /**
     * @return void
     */
    public function testAllowedMethods() : void
    {
        $expected = ['foo', 'bar'];

        $exception = new MethodNotAllowedException('blah', [
            'allowed' => $expected,
        ]);

        $this->assertSame($expected, $exception->getAllowedMethods());
    }
}
