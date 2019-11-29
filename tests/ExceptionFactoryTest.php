<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\ExceptionFactory;
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\MiddlewareAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteAlreadyExistsException;
use Sunrise\Http\Router\Exception\RouteNotFoundException;

/**
 * ExceptionFactoryTest
 */
class ExceptionFactoryTest extends TestCase
{

    /**
     * @return void
     */
    public function testInvalidLoaderFileResource() : void
    {
        $exception = (new ExceptionFactory)->invalidLoaderFileResource('foo', [
            'foo' => 'bar',
        ]);

        $expectedContext = [
            'foo' => 'bar',
            'resource' => 'foo',
        ];

        $expectedMessage = 'The resource "foo" is not found.';

        $this->assertInstanceOf(InvalidLoaderResourceException::class, $exception);
        $this->assertSame($expectedContext, $exception->getContext());
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testMethodNotAllowed() : void
    {
        $exception = (new ExceptionFactory)->methodNotAllowed('foo', ['bar'], [
            'foo' => 'bar',
        ]);

        $expectedContext = [
            'foo' => 'bar',
            'method' => 'foo',
            'allowed' => ['bar'],
        ];

        $expectedMessage = 'The method "foo" is not allowed.';

        $this->assertInstanceOf(MethodNotAllowedException::class, $exception);
        $this->assertSame($expectedContext, $exception->getContext());
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testMiddlewareAlreadyExists() : void
    {
        $exception = (new ExceptionFactory)->middlewareAlreadyExists('foo', [
            'foo' => 'bar',
        ]);

        $expectedContext = [
            'foo' => 'bar',
            'hash' => 'foo',
        ];

        $expectedMessage = 'A middleware with the hash "foo" already exists.';

        $this->assertInstanceOf(MiddlewareAlreadyExistsException::class, $exception);
        $this->assertSame($expectedContext, $exception->getContext());
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testRouteAlreadyExists() : void
    {
        $exception = (new ExceptionFactory)->routeAlreadyExists('foo', [
            'foo' => 'bar',
        ]);

        $expectedContext = [
            'foo' => 'bar',
            'name' => 'foo',
        ];

        $expectedMessage = 'A route with the name "foo" already exists.';

        $this->assertInstanceOf(RouteAlreadyExistsException::class, $exception);
        $this->assertSame($expectedContext, $exception->getContext());
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testRouteNotFoundByName() : void
    {
        $exception = (new ExceptionFactory)->routeNotFoundByName('foo', [
            'foo' => 'bar',
        ]);

        $expectedContext = [
            'foo' => 'bar',
            'name' => 'foo',
        ];

        $expectedMessage = 'No route found for the name "foo".';

        $this->assertInstanceOf(RouteNotFoundException::class, $exception);
        $this->assertSame($expectedContext, $exception->getContext());
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testRouteNotFoundByUri() : void
    {
        $exception = (new ExceptionFactory)->routeNotFoundByUri('foo', [
            'foo' => 'bar',
        ]);

        $expectedContext = [
            'foo' => 'bar',
            'uri' => 'foo',
        ];

        $expectedMessage = 'No route found for the URI "foo".';

        $this->assertInstanceOf(RouteNotFoundException::class, $exception);
        $this->assertSame($expectedContext, $exception->getContext());
        $this->assertSame($expectedMessage, $exception->getMessage());
    }
}
