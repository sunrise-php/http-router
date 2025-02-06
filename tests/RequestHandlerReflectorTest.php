<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\RequestHandlerReflector;

final class RequestHandlerReflectorTest extends TestCase
{
    public function testReflectObject(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $reflectedRequestHandler = (new RequestHandlerReflector())->reflectRequestHandler($requestHandler);
        $this->assertInstanceOf(ReflectionClass::class, $reflectedRequestHandler);
        $this->assertSame($requestHandler::class, $reflectedRequestHandler->name);
    }

    public function testReflectClass(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $reflectedRequestHandler = (new RequestHandlerReflector())->reflectRequestHandler($requestHandler::class);
        $this->assertInstanceOf(ReflectionClass::class, $reflectedRequestHandler);
        $this->assertSame($requestHandler::class, $reflectedRequestHandler->name);
    }

    public function testReflectObjectMethod(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $reflectedRequestHandler = (new RequestHandlerReflector())->reflectRequestHandler([$requestHandler, 'expects']);
        $this->assertInstanceOf(ReflectionMethod::class, $reflectedRequestHandler);
        $this->assertSame($requestHandler::class, $reflectedRequestHandler->class);
        $this->assertSame('expects', $reflectedRequestHandler->name);
    }

    public function testReflectClassMethod(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $reflectedRequestHandler = (new RequestHandlerReflector())->reflectRequestHandler([$requestHandler::class, 'expects']);
        $this->assertInstanceOf(ReflectionMethod::class, $reflectedRequestHandler);
        $this->assertSame($requestHandler::class, $reflectedRequestHandler->class);
        $this->assertSame('expects', $reflectedRequestHandler->name);
    }

    public function testReflectUnknownClass(): void
    {
        $requestHandlerReflector = new RequestHandlerReflector();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/could not be reflected/');
        $requestHandlerReflector->reflectRequestHandler(['Unknown', 'unknown']);
    }

    public function testReflectUnknownMethod(): void
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandlerReflector = new RequestHandlerReflector();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/could not be reflected/');
        $requestHandlerReflector->reflectRequestHandler([$requestHandler, 'unknown']);
    }

    public function testReflectInvalidReference(): void
    {
        $requestHandlerReflector = new RequestHandlerReflector();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/could not be reflected/');
        $requestHandlerReflector->reflectRequestHandler(null);
    }
}
