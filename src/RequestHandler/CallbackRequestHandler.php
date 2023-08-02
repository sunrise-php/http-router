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
use Sunrise\Http\Router\ParameterResolutionerInterface;
use Sunrise\Http\Router\ParameterResolver\ObjectInjectionParameterResolver;
use Sunrise\Http\Router\ResponseResolutionerInterface;

use function Sunrise\Http\Router\reflect_callable;

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
     * The callback's parameter resolutioner
     *
     * @var ParameterResolutionerInterface
     */
    private ParameterResolutionerInterface $parameterResolutioner;

    /**
     * The callback's response resolutioner
     *
     * @var ResponseResolutionerInterface
     */
    private ResponseResolutionerInterface $responseResolutioner;

    /**
     * Constructor of the class
     *
     * @param callable $callback
     * @param ParameterResolutionerInterface $parameterResolutioner
     * @param ResponseResolutionerInterface $responseResolutioner
     */
    public function __construct(
        callable $callback,
        ParameterResolutionerInterface $parameterResolutioner,
        ResponseResolutionerInterface $responseResolutioner
    ) {
        $this->callback = $callback;
        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;
    }

    /**
     * Gets the callback's reflection
     *
     * @return ReflectionFunction|ReflectionMethod
     */
    public function getReflection(): ReflectionFunction|ReflectionMethod
    {
        return reflect_callable($this->callback);
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $source = $this->getReflection();

        $arguments = $this->parameterResolutioner
            ->withContext($request)
            ->withPriorityResolver(
                new ObjectInjectionParameterResolver($request),
            )
            ->resolveParameters(...$source->getParameters());

        /** @var mixed $response */
        $response = ($this->callback)(...$arguments);

        return $this->responseResolutioner->resolveResponse($response, $request, $source);
    }
}
