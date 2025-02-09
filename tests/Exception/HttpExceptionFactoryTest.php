<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Dictionary\ErrorMessage;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Throwable;

final class HttpExceptionFactoryTest extends TestCase
{
    public function testMalformedUri(): void
    {
        $exception = HttpExceptionFactory::malformedUri();
        self::assertSame(400, $exception->getCode());
        self::assertSame(ErrorMessage::MALFORMED_URI, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::malformedUri('foo', 500, $previous);
        self::assertSame(500, $exception->getCode());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testResourceNotFound(): void
    {
        $exception = HttpExceptionFactory::resourceNotFound();
        self::assertSame(404, $exception->getCode());
        self::assertSame(ErrorMessage::RESOURCE_NOT_FOUND, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::resourceNotFound('foo', 500, $previous);
        self::assertSame(500, $exception->getCode());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testMethodNotAllowed(): void
    {
        $exception = HttpExceptionFactory::methodNotAllowed();
        self::assertSame(405, $exception->getCode());
        self::assertSame(ErrorMessage::METHOD_NOT_ALLOWED, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::methodNotAllowed('foo', 500, $previous);
        self::assertSame(500, $exception->getCode());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testInvalidVariable(): void
    {
        $exception = HttpExceptionFactory::invalidVariable();
        self::assertSame(400, $exception->getCode());
        self::assertSame(ErrorMessage::INVALID_VARIABLE, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::invalidVariable('foo', 500, $previous);
        self::assertSame(500, $exception->getCode());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testInvalidQuery(): void
    {
        $exception = HttpExceptionFactory::invalidQuery();
        self::assertSame(400, $exception->getCode());
        self::assertSame(ErrorMessage::INVALID_QUERY, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::invalidQuery('foo', 500, $previous);
        self::assertSame(500, $exception->getCode());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testMissingHeader(): void
    {
        $exception = HttpExceptionFactory::missingHeader();
        self::assertSame(400, $exception->getCode());
        self::assertSame(ErrorMessage::MISSING_HEADER, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::missingHeader('foo', 500, $previous);
        self::assertSame(500, $exception->getCode());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testInvalidHeader(): void
    {
        $exception = HttpExceptionFactory::invalidHeader();
        self::assertSame(400, $exception->getCode());
        self::assertSame(ErrorMessage::INVALID_HEADER, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::invalidHeader('foo', 500, $previous);
        self::assertSame(500, $exception->getCode());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testMissingCookie(): void
    {
        $exception = HttpExceptionFactory::missingCookie();
        self::assertSame(400, $exception->getCode());
        self::assertSame(ErrorMessage::MISSING_COOKIE, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::missingCookie('foo', 500, $previous);
        self::assertSame(500, $exception->getCode());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testInvalidCookie(): void
    {
        $exception = HttpExceptionFactory::invalidCookie();
        self::assertSame(400, $exception->getCode());
        self::assertSame(ErrorMessage::INVALID_COOKIE, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::invalidCookie('foo', 500, $previous);
        self::assertSame(500, $exception->getCode());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testInvalidBody(): void
    {
        $exception = HttpExceptionFactory::invalidBody();
        self::assertSame(400, $exception->getCode());
        self::assertSame(ErrorMessage::INVALID_BODY, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::invalidBody('foo', 500, $previous);
        self::assertSame(500, $exception->getCode());
        self::assertSame('foo', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }
}
