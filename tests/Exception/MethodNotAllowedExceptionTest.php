<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;

/**
 * Import functions
 */
use function implode;

/**
 * MethodNotAllowedExceptionTest
 */
class MethodNotAllowedExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $exception = new MethodNotAllowedException();

        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @return void
     */
    public function testMethod() : void
    {
        $expected = 'foo';

        $exception = new MethodNotAllowedException('blah', [
            'method' => $expected,
        ]);

        $this->assertSame($expected, $exception->getMethod());
    }

    /**
     * @return void
     */
    public function testMethodWithEmptyContext() : void
    {
        $exception = new MethodNotAllowedException('blah');

        $this->assertSame('', $exception->getMethod());
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

    /**
     * @return void
     */
    public function testAllowedMethodsWithEmptyContext() : void
    {
        $exception = new MethodNotAllowedException('blah');

        $this->assertSame([], $exception->getAllowedMethods());
    }

    /**
     * @return void
     */
    public function testGluingAllowedMethods() : void
    {
        $methods = ['foo', 'bar'];

        $expected = implode(',', $methods);

        $exception = new MethodNotAllowedException('blah', [
            'allowed' => $methods,
        ]);

        $this->assertSame($expected, $exception->getJoinedAllowedMethods());
    }

    /**
     * @return void
     */
    public function testGluingAllowedMethodsWithEmptyContext() : void
    {
        $exception = new MethodNotAllowedException('blah');

        $this->assertSame('', $exception->getJoinedAllowedMethods());
    }
}
