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
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\ParameterResolver\DirectObjectInjectionParameterResolver;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;

use function class_exists;
use function is_array;
use function is_callable;
use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * @since 3.0.0
 */
final class RequestHandlerResolver implements RequestHandlerResolverInterface
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
    public function resolveRequestHandler(mixed $reference): RequestHandlerInterface
    {
        if ($reference instanceof RequestHandlerInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallableRequestHandler($this->createCallback($reference, new ReflectionFunction($reference)));
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($reference) && is_callable($reference, true)) {
            /** @var array{0: string|object, 1: string} $reference */
            if (is_string($reference[0]) && class_exists($reference[0])) {
                $reference[0] = $this->classResolver->resolveClass($reference[0]);
            }

            if (is_callable($reference)) {
                /** @var array{0: class-string|object, 1: non-empty-string} $reference */
                $reflection = new ReflectionMethod($reference[0], $reference[1]);

                /** @var callable $reference */

                return new CallableRequestHandler($this->createCallback($reference, $reflection));
            }
        }

        if (is_string($reference) && is_subclass_of($reference, RequestHandlerInterface::class)) {
            return $this->classResolver->resolveClass($reference);
        }

        throw new InvalidArgumentException(sprintf(
            'The request handler reference %s could not be resolved.',
            ReferenceResolver::stringifyReference($reference),
        ));
    }

    /**
     * @return callable(ServerRequestInterface): ResponseInterface
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    private function createCallback(callable $callback, ReflectionMethod|ReflectionFunction $reflection): callable
    {
        return fn(ServerRequestInterface $request): ResponseInterface => (
            $this->responseResolverChain->resolveResponse(
                $callback(
                    ...$this->parameterResolverChain
                        ->withContext($request)
                        ->withResolver(
                            new DirectObjectInjectionParameterResolver($request),
                        )
                        ->resolveParameters(...$reflection->getParameters())
                ),
                $reflection,
                $request,
            )
        );
    }
}
