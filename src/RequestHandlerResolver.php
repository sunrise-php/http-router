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
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use Sunrise\Http\Router\ParameterResolver\ObjectInjectionParameterResolver;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;

use function is_array;
use function is_callable;
use function is_string;
use function is_subclass_of;

/**
 * @since 2.10.0
 */
final class RequestHandlerResolver
{
    private readonly ClassResolver $classResolver;

    public function __construct(
        private readonly ParameterResolver $parameterResolver,
        private readonly ResponseResolver $responseResolver,
    ) {
        $this->classResolver = new ClassResolver($parameterResolver);
    }

    /**
     * @throws InvalidArgumentException If the requst handler couldn't be resolved.
     */
    public function resolveRequestHandler(mixed $requestHandler): RequestHandlerInterface
    {
        if ($requestHandler instanceof RequestHandlerInterface) {
            return $requestHandler;
        }

        if ($requestHandler instanceof Closure) {
            return new CallableRequestHandler($this->createRequestHandlerCallback($requestHandler, new ReflectionFunction($requestHandler)));
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($requestHandler) && is_callable($requestHandler, true)) {
            /** @var array{0: class-string|object, 1: non-empty-string} $requestHandler */

            if (is_string($requestHandler[0])) {
                $requestHandler[0] = $this->classResolver->resolveClass($requestHandler[0]);
            }

            if (is_callable($requestHandler)) {
                return new CallableRequestHandler($this->createRequestHandlerCallback($requestHandler, new ReflectionMethod($requestHandler[0], $requestHandler[1])));
            }
        }

        if (is_string($requestHandler) && is_subclass_of($requestHandler, RequestHandlerInterface::class)) {
            return $this->classResolver->resolveClass($requestHandler);
        }

        throw new InvalidArgumentException('Unsupported request handler.');
    }

    /**
     * @throws InvalidArgumentException If the middleware couldn't be resolved.
     */
    public function resolveMiddleware(mixed $middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        if ($middleware instanceof Closure) {
            return new CallableMiddleware($this->createMiddlewareCallback($middleware, new ReflectionFunction($middleware)));
        }

        if (is_string($middleware) && is_subclass_of($middleware, MiddlewareInterface::class)) {
            return $this->classResolver->resolveClass($middleware);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($middleware) && is_callable($middleware, true)) {
            /** @var array{0: class-string|object, 1: non-empty-string} $middleware */

            if (is_string($middleware[0])) {
                $middleware[0] = $this->classResolver->resolveClass($middleware[0]);
            }

            if (is_callable($middleware)) {
                return new CallableMiddleware($this->createMiddlewareCallback($middleware, new ReflectionMethod($middleware[0], $middleware[1])));
            }
        }

        throw new InvalidArgumentException('Unsupported middleware.');
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
                $callback(...
                    $this->parameterResolver
                        ->withContext($request)
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
}
