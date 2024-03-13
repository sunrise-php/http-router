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

namespace Sunrise\Http\Router\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\ParameterResolver\ObjectInjectionParameterResolver;
use Sunrise\Http\Router\ParameterResolverChainInterface;
use Sunrise\Http\Router\ResponseResolverChainInterface;

/**
 * @since 3.0.0
 */
final class CallbackMiddleware implements MiddlewareInterface
{
    /** @var callable */
    private $callback;

    public function __construct(
        callable $callback,
        private readonly ReflectionMethod|ReflectionFunction $callbackReflection,
        private readonly ParameterResolverChainInterface $parameterResolver,
        private readonly ResponseResolverChainInterface $responseResolver,
    ) {
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $arguments = $this->parameterResolver
            ->withContext($request)
            ->withPriorityResolver(
                new ObjectInjectionParameterResolver($request),
                new ObjectInjectionParameterResolver($handler),
            )
            ->resolveParameters(...$this->callbackReflection->getParameters());

        return $this->responseResolver->resolveResponse(
            ($this->callback)(...$arguments),
            $this->callbackReflection,
            $request,
        );
    }
}
