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

use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use ReflectionMethod;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use Sunrise\Http\Router\ParameterResolver\DirectInjectionParameterResolver;

use function is_array;
use function is_callable;
use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * @since 3.0.0
 */
final class MiddlewareResolver implements MiddlewareResolverInterface
{
    public function __construct(
        private readonly ClassResolverInterface $classResolver,
        private readonly ParameterResolverChainInterface $parameterResolverChain,
        private readonly ResponseResolverChainInterface $responseResolverChain,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws ReflectionException
     */
    public function resolveMiddleware(mixed $reference): MiddlewareInterface
    {
        if ($reference instanceof MiddlewareInterface) {
            return $reference;
        }

        if (is_string($reference) && is_subclass_of($reference, MiddlewareInterface::class)) {
            return $this->classResolver->resolveClass($reference);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($reference) && is_callable($reference, true)) {
            /** @var array{0: class-string|object, 1: string} $reference */

            if (is_string($reference[0])) {
                $reference[0] = $this->classResolver->resolveClass($reference[0]);
            }

            if (is_callable($reference)) {
                return $this->resolveCallback($reference, new ReflectionMethod($reference[0], $reference[1]));
            }
        }

        throw new InvalidArgumentException(sprintf(
            'The middleware reference "%s" could not be resolved.',
            ReferenceResolver::stringifyReference($reference),
        ));
    }

    /**
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    private function resolveCallback(callable $callback, ReflectionMethod $reflection): MiddlewareInterface
    {
        return new CallableMiddleware(
            fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => (
                $this->responseResolverChain->resolveResponse(
                    $callback(
                        ...$this->parameterResolverChain
                            ->withContext($request)
                            ->withResolver(
                                new DirectInjectionParameterResolver($request),
                                new DirectInjectionParameterResolver($handler),
                            )
                            ->resolveParameters(...$reflection->getParameters())
                    ),
                    $reflection,
                    $request,
                )
            )
        );
    }
}
