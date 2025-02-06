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
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(ErrorMessage::MALFORMED_URI, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::malformedUri('foo', 500, $previous);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testResourceNotFound(): void
    {
        $exception = HttpExceptionFactory::resourceNotFound();
        $this->assertSame(404, $exception->getCode());
        $this->assertSame(ErrorMessage::RESOURCE_NOT_FOUND, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::resourceNotFound('foo', 500, $previous);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testMethodNotAllowed(): void
    {
        $exception = HttpExceptionFactory::methodNotAllowed();
        $this->assertSame(405, $exception->getCode());
        $this->assertSame(ErrorMessage::METHOD_NOT_ALLOWED, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::methodNotAllowed('foo', 500, $previous);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testInvalidVariable(): void
    {
        $exception = HttpExceptionFactory::invalidVariable();
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(ErrorMessage::INVALID_VARIABLE, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::invalidVariable('foo', 500, $previous);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testInvalidQuery(): void
    {
        $exception = HttpExceptionFactory::invalidQuery();
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(ErrorMessage::INVALID_QUERY, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::invalidQuery('foo', 500, $previous);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testMissingHeader(): void
    {
        $exception = HttpExceptionFactory::missingHeader();
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(ErrorMessage::MISSING_HEADER, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::missingHeader('foo', 500, $previous);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testInvalidHeader(): void
    {
        $exception = HttpExceptionFactory::invalidHeader();
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(ErrorMessage::INVALID_HEADER, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::invalidHeader('foo', 500, $previous);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testMissingCookie(): void
    {
        $exception = HttpExceptionFactory::missingCookie();
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(ErrorMessage::MISSING_COOKIE, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::missingCookie('foo', 500, $previous);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testInvalidCookie(): void
    {
        $exception = HttpExceptionFactory::invalidCookie();
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(ErrorMessage::INVALID_COOKIE, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::invalidCookie('foo', 500, $previous);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testInvalidBody(): void
    {
        $exception = HttpExceptionFactory::invalidBody();
        $this->assertSame(400, $exception->getCode());
        $this->assertSame(ErrorMessage::INVALID_BODY, $exception->getMessageTemplate());

        $previous = $this->createMock(Throwable::class);
        $exception = HttpExceptionFactory::invalidBody('foo', 500, $previous);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('foo', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
