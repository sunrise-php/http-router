<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use Closure;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionParameter;
use Sunrise\Http\Router\LanguageInterface;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\MediaTypeInterface;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\RouteInterface;
use Symfony\Component\Validator\ConstraintViolationInterface as ValidatorConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

use function is_int;
use function is_null;

/**
 * @psalm-require-extends TestCase
 * @phpstan-require-extends TestCase
 */
trait TestKit
{
    protected function mockChainBreakingMiddleware(
        ResponseInterface $response,
        ?ServerRequestInterface $request = null,
        ?RequestHandlerInterface $handler = null,
        int|InvocationOrder|null $calls = null,
    ): MiddlewareInterface&MockObject {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects(self::normalizeInvocationOrder($calls))->method('process')->with($request ?? self::anything(), $handler ?? self::anything())->willReturnCallback(self::createChainBreakingCallbackMiddleware($response));
        return $middleware;
    }

    /**
     * @return Generator<array-key, MiddlewareInterface&MockObject>
     */
    protected function mockChainBreakingMiddlewares(
        int $count,
        ResponseInterface $response,
        ?ServerRequestInterface $request = null,
        ?RequestHandlerInterface $handler = null,
        int|InvocationOrder|null $calls = null,
    ): Generator {
        for ($i = 0; $i < $count; $i++) {
            yield $this->mockChainBreakingMiddleware($response, $request, $handler, $calls);
        }
    }

    protected function mockChainContinuingMiddleware(
        ?ServerRequestInterface $request = null,
        ?RequestHandlerInterface $handler = null,
        int|InvocationOrder|null $calls = null,
    ): MiddlewareInterface&MockObject {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects(self::normalizeInvocationOrder($calls))->method('process')->with($request ?? self::anything(), $handler ?? self::anything())->willReturnCallback(self::createChainContinuingCallbackMiddleware());
        return $middleware;
    }

    /**
     * @return Generator<array-key, MiddlewareInterface&MockObject>
     */
    protected function mockChainContinuingMiddlewares(
        int $count,
        ?ServerRequestInterface $request = null,
        ?RequestHandlerInterface $handler = null,
        int|InvocationOrder|null $calls = null,
    ): Generator {
        for ($i = 0; $i < $count; $i++) {
            yield $this->mockChainContinuingMiddleware($request, $handler, $calls);
        }
    }

    protected function mockLanguage(
        string $code,
        int|InvocationOrder|null $calls = null,
    ): LanguageInterface&MockObject {
        $locale = $this->createMock(LanguageInterface::class);
        $locale->expects(self::normalizeInvocationOrder($calls))->method('getCode')->willReturn($code);
        return $locale;
    }

    protected function mockLoader(
        array $routes,
        int|InvocationOrder|null $calls = null,
    ): LoaderInterface&MockObject {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::normalizeInvocationOrder($calls))->method('load')->willReturn($routes);
        return $loader;
    }

    protected function mockMediaType(
        string $identifier,
        int|InvocationOrder|null $calls = null,
    ): MediaTypeInterface&MockObject {
        $mediaType = $this->createMock(MediaTypeInterface::class);
        $mediaType->expects(self::normalizeInvocationOrder($calls))->method('getIdentifier')->willReturn($identifier);
        return $mediaType;
    }

    protected function mockParameterResolver(
        string $name,
        string $value,
        mixed $context = null,
        int $weight = 0,
        int|InvocationOrder|null $calls = null,
        ?array &$registry = null,
    ): ParameterResolverInterface&MockObject {
        $resolver = $this->createMock(ParameterResolverInterface::class);
        $resolver->expects(self::normalizeInvocationOrder($calls))->method('resolveParameter')->with(self::anything(), $context)->willReturnCallback(static fn(ReflectionParameter $parameter): Generator => $parameter->name === $name ? yield $value : null);
        $resolver->expects(self::any())->method('getWeight')->willReturn($weight);

        if ($registry !== null) {
            $registry[] = $resolver;
        }

        return $resolver;
    }

    protected function mockRoute(
        string $name,
        string $path = '/',
        array $methods = ['GET'],
        mixed $requestHandler = null,
        array $middlewares = [],
        int|InvocationOrder|null $nameCalls = null,
        int|InvocationOrder|null $pathCalls = null,
        int|InvocationOrder|null $methodsCalls = null,
        int|InvocationOrder|null $requestHandlerCalls = null,
        int|InvocationOrder|null $middlewaresCalls = null,
    ): RouteInterface&MockObject {
        $route = $this->createMock(RouteInterface::class);
        $route->expects(self::normalizeInvocationOrder($nameCalls))->method('getName')->willReturn($name);
        $route->expects(self::normalizeInvocationOrder($pathCalls))->method('getPath')->willReturn($path);
        $route->expects(self::normalizeInvocationOrder($methodsCalls))->method('getMethods')->willReturn($methods);
        $route->expects(self::normalizeInvocationOrder($requestHandlerCalls))->method('getRequestHandler')->willReturn($requestHandler);
        $route->expects(self::normalizeInvocationOrder($middlewaresCalls))->method('getMiddlewares')->willReturn($middlewares);
        return $route;
    }

    protected function mockServerRequest(
        string $method = 'GET',
        string $path = '/',
        string $body = '',
        int|InvocationOrder|null $methodCalls = null,
        int|InvocationOrder|null $pathCalls = null,
        int|InvocationOrder|null $bodyCalls = null,
    ): ServerRequestInterface&MockObject {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::normalizeInvocationOrder($methodCalls))->method('getMethod')->willReturn($method);
        $uri = $this->createMock(UriInterface::class);
        $uri->expects(self::normalizeInvocationOrder($pathCalls))->method('getPath')->willReturn($path);
        $request->expects(self::any())->method('getUri')->willReturn($uri);
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects(self::any())->method('__toString')->willReturn($body);
        $request->expects(self::normalizeInvocationOrder($bodyCalls))->method('getBody')->willReturn($stream);
        return $request;
    }

    protected function mockValidatorConstraintViolation(
        string $message,
        string $propertyPath,
        string $code,
        mixed $invalidValue,
        int|InvocationOrder|null $messageCalls = null,
        int|InvocationOrder|null $propertyPathCalls = null,
        int|InvocationOrder|null $codeCalls = null,
        int|InvocationOrder|null $invalidValueCalls = null,
    ): ValidatorConstraintViolationInterface&MockObject {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects(self::normalizeInvocationOrder($messageCalls))->method('getMessage')->willReturn($message);
        $violation->expects(self::normalizeInvocationOrder($propertyPathCalls))->method('getPropertyPath')->willReturn($propertyPath);
        $violation->expects(self::normalizeInvocationOrder($codeCalls))->method('getCode')->willReturn($code);
        $violation->expects(self::normalizeInvocationOrder($invalidValueCalls))->method('getInvalidValue')->willReturn($invalidValue);
        return $violation;
    }

    protected static function createChainBreakingCallbackMiddleware(ResponseInterface $response): Closure
    {
        return static fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => $response;
    }

    protected static function createChainContinuingCallbackMiddleware(): Closure
    {
        return static fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => $handler->handle($request);
    }

    private static function normalizeInvocationOrder(int|InvocationOrder|null $invocationOrder): InvocationOrder
    {
        if (is_null($invocationOrder)) {
            return self::any();
        }
        if (is_int($invocationOrder)) {
            return self::exactly($invocationOrder);
        }

        return $invocationOrder;
    }
}
