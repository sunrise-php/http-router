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
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
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
final class InterceptorResolver
{
    /**
     * @var array<class-string, object>
     */
    private array $resolvedClasses = [];

    public function __construct(
        private readonly ParameterResolver $parameterResolver,
        private readonly ResponseResolver $responseResolver,
    ) {
    }

    /**
     * @throws LogicException If the requst handler couldn't be resolved.
     */
    public function resolveRequestHandler(mixed $requestHandler): RequestHandlerInterface
    {
        if ($requestHandler instanceof RequestHandlerInterface) {
            return $requestHandler;
        }

        if ($requestHandler instanceof Closure) {
            return new CallableRequestHandler(
                $this->createRequestHandlerCallback($requestHandler, new ReflectionFunction($requestHandler)),
            );
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($requestHandler) && is_callable($requestHandler, true)) {
            /** @var array{0: class-string|object, 1: non-empty-string} $requestHandler */

            if (is_string($requestHandler[0])) {
                $requestHandler[0] = $this->resolveClass($requestHandler[0]);
            }

            if (is_callable($requestHandler)) {
                return new CallableRequestHandler(
                    $this->createRequestHandlerCallback($requestHandler, new ReflectionMethod($requestHandler[0], $requestHandler[1])),
                );
            }
        }

        if (is_string($requestHandler) && is_subclass_of($requestHandler, RequestHandlerInterface::class)) {
            return $this->resolveClass($requestHandler);
        }

        throw new LogicException(sprintf(
            'The request handler %s could not be resolved.',
            self::stringifyInterceptor($requestHandler),
        ));
    }

    /**
     * @throws LogicException If the middleware couldn't be resolved.
     */
    public function resolveMiddleware(mixed $middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        if ($middleware instanceof Closure) {
            return new CallableMiddleware(
                $this->createMiddlewareCallback($middleware, new ReflectionFunction($middleware)),
            );
        }

        if (is_string($middleware) && is_subclass_of($middleware, MiddlewareInterface::class)) {
            return $this->resolveClass($middleware);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($middleware) && is_callable($middleware, true)) {
            /** @var array{0: class-string|object, 1: non-empty-string} $middleware */

            if (is_string($middleware[0])) {
                $middleware[0] = $this->resolveClass($middleware[0]);
            }

            if (is_callable($middleware)) {
                return new CallableMiddleware(
                    $this->createMiddlewareCallback($middleware, new ReflectionMethod($middleware[0], $middleware[1])),
                );
            }
        }

        throw new LogicException(sprintf(
            'The middleware %s could not be resolved.',
            self::stringifyInterceptor($middleware),
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
                $callback(...
                    $this->parameterResolver
                        ->withContext($request)
                        ->withPriorityResolver(new ObjectInjectionParameterResolver($request))
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
                $callback(...
                    $this->parameterResolver
                        ->withContext($request)
                        ->withPriorityResolver(new ObjectInjectionParameterResolver($request))
                        ->withPriorityResolver(new ObjectInjectionParameterResolver($handler))
                        ->resolveParameters(...$reflection->getParameters())
                ),
                $reflection,
                $request,
            )
        );
    }

    /**
     * Resolves the given named class
     *
     * @param class-string<T> $className
     *
     * @return T
     *
     * @template T of object
     *
     * @throws LogicException If the class couldn't be resolved.
     */
    private function resolveClass(string $className): object
    {
        if (isset($this->resolvedClasses[$className])) {
            /** @var T */
            return $this->resolvedClasses[$className];
        }

        if (!class_exists($className)) {
            throw new LogicException(sprintf('The class %s does not exist.', $className));
        }

        $classReflection = new ReflectionClass($className);
        if (!$classReflection->isInstantiable()) {
            throw new LogicException(sprintf('The class %s is not instantiable.', $className));
        }

        $arguments = [];
        $constructor = $classReflection->getConstructor();
        if (isset($constructor) && $constructor->getNumberOfParameters()) {
            $arguments = $this->parameterResolver->resolveParameters(...$constructor->getParameters());
        }

        /** @var T */
        return $this->resolvedClasses[$className] = $classReflection->newInstance(...$arguments);
    }

    private static function stringifyInterceptor(mixed $interceptor): string
    {
        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_callable($interceptor, true, $result)) {
            return $result;
        }

        return get_debug_type($interceptor);
    }
}
