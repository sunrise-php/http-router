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
        self::assertSame('foo', $exception->getMessage());
        self::assertSame(400, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testMessagePlaceholders(): void
    {
        $exception = new HttpException('foo {bar}, {bar}! {baz}!', 400);
        $exception->addMessagePlaceholder('{bar}', 1);
        $exception->addMessagePlaceholder('{baz}', 2);
        self::assertSame('foo 1, 1! 2!', $exception->getMessage());
        self::assertSame('foo {bar}, {bar}! {baz}!', $exception->getMessageTemplate());
        self::assertSame(['{bar}' => 1, '{baz}' => 2], $exception->getMessagePlaceholders());
    }

    public function testHeaderFields(): void
    {
        $qux = new class implements Stringable
        {
            public function __toString(): string
            {
                return 'qux';
            }
        };

        $exception = new HttpException('foo', 400);
        $exception->addHeaderField('x-foo', 'bar');
        $exception->addHeaderField('x-bar', 'baz', $qux);
        self::assertSame([['x-foo', 'bar'], ['x-bar', 'baz, qux']], $exception->getHeaderFields());
    }

    public function testConstraintViolations(): void
    {
        $exception = new HttpException('foo', 400);
        $foo = $this->createMock(ConstraintViolationInterface::class);
        $bar = $this->createMock(ConstraintViolationInterface::class);
        $exception->addConstraintViolation($foo, $bar);
        self::assertSame([$foo, $bar], $exception->getConstraintViolations());
    }
}
