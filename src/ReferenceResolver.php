<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Exception\InvalidReferenceException;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use Sunrise\Http\Router\ParameterResolver\ObjectInjectionParameterResolver;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;

use function class_exists;
use function get_debug_type;
use function is_array;
use function is_callable;
use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * @since 2.10.0
 */
final class ReferenceResolver
{
    public function __construct(
        private readonly ParameterResolver $parameterResolver,
        private readonly ClassResolver $classResolver,
        private readonly ResponseResolver $responseResolver,
    ) {
    }

    /**
     * @throws InvalidReferenceException If the requst handler couldn't be resolved.
     */
    public function resolveRequestHandler(mixed $reference): RequestHandlerInterface
    {
        if ($reference instanceof RequestHandlerInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallableRequestHandler($this->createRequestHandlerCallback($reference, new ReflectionFunction($reference)));
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($reference) && is_callable($reference, true)) {
            /** @var array{0: string|object, 1: string} $reference */

            if (is_string($reference[0]) && class_exists($reference[0])) {
                $reference[0] = $this->classResolver->resolveClass($reference[0]);
            }

            if (is_callable($reference)) {
                /** @var array{0: class-string|object, 1: non-empty-string} $reference */

                return new CallableRequestHandler($this->createRequestHandlerCallback($reference, new ReflectionMethod($reference[0], $reference[1])));
            }
        }

        if (is_string($reference) && is_subclass_of($reference, RequestHandlerInterface::class)) {
            return $this->classResolver->resolveClass($reference);
        }

        throw new InvalidReferenceException(sprintf(
            'The request handler reference %s could not be resolved.',
            self::stringifyReference($reference),
        ));
    }

    /**
     * @throws InvalidReferenceException If the middleware couldn't be resolved.
     */
    public function resolveMiddleware(mixed $reference): MiddlewareInterface
    {
        if ($reference instanceof MiddlewareInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallableMiddleware($this->createMiddlewareCallback($reference, new ReflectionFunction($reference)));
        }

        if (is_string($reference) && is_subclass_of($reference, MiddlewareInterface::class)) {
            return $this->classResolver->resolveClass($reference);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($reference) && is_callable($reference, true)) {
            /** @var array{0: string|object, 1: string} $reference */

            if (is_string($reference[0]) && class_exists($reference[0])) {
                $reference[0] = $this->classResolver->resolveClass($reference[0]);
            }

            if (is_callable($reference)) {
                /** @var array{0: class-string|object, 1: non-empty-string} $reference */

                return new CallableMiddleware($this->createMiddlewareCallback($reference, new ReflectionMethod($reference[0], $reference[1])));
            }
        }

        throw new InvalidReferenceException(sprintf(
            'The middleware reference %s could not be resolved.',
            self::stringifyReference($reference),
        ));
    }

    /**
     * @return Closure(ServerRequestInterface=): ResponseInterface
     */
    private function createRequestHandlerCallback(
        callable $callback,
        ReflectionMethod|ReflectionFunction $reflection,
    ): Closure {
        return fn(ServerRequestInterface $request): ResponseInterface => (
            $this->responseResolver->resolveResponse(
                $callback(
                    ...$this->parameterResolver
                        ->withRequest($request)
                        ->withPriorityResolver(
                            new ObjectInjectionParameterResolver($request),
                        )
                        ->resolveParameters(...$reflection->getParameters())
                ),
                $reflection,
                $request,
            )
        );
    }

    /**
     * @return Closure(ServerRequestInterface=, RequestHandlerInterface=): ResponseInterface
     */
    private function createMiddlewareCallback(
        callable $callback,
        ReflectionMethod|ReflectionFunction $reflection,
    ): Closure {
        return fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => (
            $this->responseResolver->resolveResponse(
                $callback(
                    ...$this->parameterResolver
                        ->withRequest($request)
                        ->withPriorityResolver(
                            new ObjectInjectionParameterResolver($request),
                            new ObjectInjectionParameterResolver($handler),
                        )
                        ->resolveParameters(...$reflection->getParameters())
                ),
                $reflection,
                $request,
            )
        );
    }

    private static function stringifyReference(mixed $reference): string
    {
        return is_callable($reference, true, $result) ? $result : get_debug_type($reference);
    }
}
