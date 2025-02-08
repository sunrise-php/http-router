<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use Closure;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\LanguageInterface;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\MediaTypeInterface;
use Sunrise\Http\Router\RouteInterface;

/**
 * @psalm-require-extends TestCase
 * @phpstan-require-extends TestCase
 */
trait TestKit
{
    protected function mockChainBreakingMiddleware(ResponseInterface $response, ?ServerRequestInterface $request = null, ?RequestHandlerInterface $handler = null, int $calls = 1): MiddlewareInterface&MockObject
    {
        /** @var TestCase $this */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects(self::exactly($calls))->method('process')->with($request ?? TestCase::anything(), $handler ?? TestCase::anything())->willReturnCallback(self::createChainBreakingCallbackMiddleware($response));
        return $middleware;
    }

    protected function mockChainContinuingMiddleware(?ServerRequestInterface $request = null, ?RequestHandlerInterface $handler = null, int $calls = 1): MiddlewareInterface&MockObject
    {
        /** @var TestCase $this */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects(self::exactly($calls))->method('process')->with($request ?? TestCase::anything(), $handler ?? TestCase::anything())->willReturnCallback(self::createChainContinuingCallbackMiddleware());
        return $middleware;
    }

    protected function mockLanguage(string $code): LanguageInterface&MockObject
    {
        /** @var TestCase $this */
        $locale = $this->createMock(LanguageInterface::class);
        $locale->method('getCode')->willReturn($code);
        return $locale;
    }

    protected function mockLoader(array $routes, int $calls = 1): LoaderInterface&MockObject
    {
        /** @var TestCase $this */
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(TestCase::exactly($calls))->method('load')->willReturnCallback(static fn(): Generator => yield from $routes);
        return $loader;
    }

    protected function mockMediaType(string $identifier): MediaTypeInterface&MockObject
    {
        /** @var TestCase $this */
        $mediaType = $this->createMock(MediaTypeInterface::class);
        $mediaType->method('getIdentifier')->willReturn($identifier);
        return $mediaType;
    }

    protected function mockRoute(string $name, string $path = ''): RouteInterface&MockObject
    {
        /** @var TestCase $this */
        $route = $this->createMock(RouteInterface::class);
        $route->method('getName')->willReturn($name);
        $route->method('getPath')->willReturn($path);
        return $route;
    }

    protected static function createChainBreakingCallbackMiddleware(ResponseInterface $response): Closure
    {
        return static fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => $response;
    }

    protected static function createChainContinuingCallbackMiddleware(): Closure
    {
        return static fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => $handler->handle($request);
    }
}
