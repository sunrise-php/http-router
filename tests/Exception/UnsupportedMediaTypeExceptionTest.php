<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\Exception;
use Sunrise\Http\Router\Exception\UnsupportedMediaTypeException;

/**
 * UnsupportedMediaTypeExceptionTest
 */
class UnsupportedMediaTypeExceptionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $exception = new UnsupportedMediaTypeException();

        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @return void
     */
    public function testMessage() : void
    {
        $message = 'blah';
        $exception = new UnsupportedMediaTypeException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testContext() : void
    {
        $context = ['foo' => 'bar'];
        $exception = new UnsupportedMediaTypeException('blah', $context);

        $this->assertSame($context, $exception->getContext());
    }

    /**
     * @return void
     */
    public function testCode() : void
    {
        $code = 100;
        $exception = new UnsupportedMediaTypeException('blah', [], $code);

        $this->assertSame($code, $exception->getCode());
    }

    /**
     * @return void
     */
    public function testPrevious() : void
    {
        $previous = new \Exception();
        $exception = new UnsupportedMediaTypeException('blah', [], 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * @return void
     */
    public function testType() : void
    {
        $expected = 'application/octet-stream';

        $exception = new UnsupportedMediaTypeException('blah', [
            'type' => $expected,
        ]);

        $this->assertSame($expected, $exception->getType());
    }

    /**
     * @return void
     */
    public function testTypeWithEmptyContext() : void
    {
        $expected = '';
        $exception = new UnsupportedMediaTypeException('blah');

        $this->assertSame($expected, $exception->getType());
    }

    /**
     * @return void
     */
    public function testSupportedTypes() : void
    {
        $expected = [
            'application/json',
            'application/x-www-form-urlencoded',
        ];

        $exception = new UnsupportedMediaTypeException('blah', [
            'supported' => $expected,
        ]);

        $this->assertSame($expected, $exception->getSupportedTypes());
    }

    /**
     * @return void
     */
    public function testSupportedTypesWithEmptyContext() : void
    {
        $expected = [];
        $exception = new UnsupportedMediaTypeException('blah');

        $this->assertSame($expected, $exception->getSupportedTypes());
    }
}
