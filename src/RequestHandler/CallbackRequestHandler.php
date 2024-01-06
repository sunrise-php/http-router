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

namespace Sunrise\Http\Router\RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutionerInterface;
use Sunrise\Http\Router\ParameterResolving\ParameterResolver\ObjectInjectionParameterResolver;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutionerInterface;

/**
 * CallbackRequestHandler
 *
 * @since 3.0.0
 */
final class CallbackRequestHandler implements RequestHandlerInterface
{

    /**
     * The request handler's callback
     *
     * @var callable
     */
    private $callback;

    /**
     * Constructor of the class
     *
     * @param callable $callback
     * @param ReflectionFunction|ReflectionMethod $callbackReflection
     * @param ParameterResolutionerInterface $parameterResolutioner
     * @param ResponseResolutionerInterface $responseResolutioner
     */
    public function __construct(
        callable $callback,
        private ReflectionFunction|ReflectionMethod $callbackReflection,
        private ParameterResolutionerInterface $parameterResolutioner,
        private ResponseResolutionerInterface $responseResolutioner,
    ) {
        $this->callback = $callback;
    }

    /**
     * Gets the callback's reflection
     *
     * @return ReflectionFunction|ReflectionMethod
     */
    public function getCallbackReflection(): ReflectionFunction|ReflectionMethod
    {
        return $this->callbackReflection;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $arguments = $this->parameterResolutioner
            ->withContext($request)
            ->withPriorityResolver(
                new ObjectInjectionParameterResolver($request),
            )
            ->resolveParameters(...$this->callbackReflection->getParameters());

        // phpcs:ignore Generic.Files.LineLength
        return $this->responseResolutioner->resolveResponse($request, ($this->callback)(...$arguments), $this->callbackReflection);
    }
}
