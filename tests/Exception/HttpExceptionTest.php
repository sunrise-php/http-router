<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Stringable;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Validation\ConstraintViolationInterface;
use Throwable;

final class HttpExceptionTest extends TestCase
{
    public function testConstructor(): void
    {
        $previous = $this->createMock(Throwable::class);
        $exception = new HttpException('foo', 400, $previous);
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testMessagePlaceholders(): void
    {
        $exception = new HttpException('foo {bar}, {bar}! {baz}!', 400);
        $exception->addMessagePlaceholder('{bar}', 1);
        $exception->addMessagePlaceholder('{baz}', 2);
        $this->assertSame('foo 1, 1! 2!', $exception->getMessage());
        $this->assertSame('foo {bar}, {bar}! {baz}!', $exception->getMessageTemplate());
        $this->assertSame(['{bar}' => 1, '{baz}' => 2], $exception->getMessagePlaceholders());
    }

    public function testHeaderFields(): void
    {
        $baz = new class implements Stringable
        {
            public function __toString(): string
            {
                return 'baz';
            }
        };

        $exception = new HttpException('foo', 400);
        $exception->addHeaderField('x-foo', 'bar', $baz);
        $exception->addHeaderField('x-bar', 'baz');
        $this->assertSame([['x-foo', 'bar, baz'], ['x-bar', 'baz']], $exception->getHeaderFields());
    }

    public function testConstraintViolations(): void
    {
        $exception = new HttpException('foo', 400);
        $foo = $this->createMock(ConstraintViolationInterface::class);
        $bar = $this->createMock(ConstraintViolationInterface::class);
        $exception->addConstraintViolation($foo, $bar);
        $this->assertSame([$foo, $bar], $exception->getConstraintViolations());
    }
}
