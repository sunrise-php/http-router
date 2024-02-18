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
use Sunrise\Http\Router\ParameterResolving\ParameterResolutionerInterface;
use Sunrise\Http\Router\ParameterResolving\ParameterResolver\ObjectInjectionParameterResolver;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutionerInterface;

/**
 * @since 3.0.0
 */
final class CallbackMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(
        callable $callback,
        private readonly ReflectionFunction|ReflectionMethod $callbackReflection,
        private readonly ParameterResolutionerInterface $parameterResolutioner,
        private readonly ResponseResolutionerInterface $responseResolutioner,
    ) {
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $arguments = $this->parameterResolutioner
            ->withContext($request)
            ->withPriorityResolver(
                new ObjectInjectionParameterResolver($request),
                new ObjectInjectionParameterResolver($handler),
            )
            ->resolveParameters(...$this->callbackReflection->getParameters());

        // phpcs:ignore Generic.Files.LineLength
        return $this->responseResolutioner->resolveResponse($request, ($this->callback)(...$arguments), $this->callbackReflection);
    }
}
