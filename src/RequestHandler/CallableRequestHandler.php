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
use Sunrise\Http\Router\ParameterResolver\TypeParameterResolver;
use Sunrise\Http\Router\ParameterResolutionerInterface;
use Sunrise\Http\Router\ResponseResolutionerInterface;
use ReflectionFunction;
use ReflectionMethod;

use function Sunrise\Http\Router\reflect_callable;

/**
 * CallableRequestHandler
 */
final class CallableRequestHandler implements RequestHandlerInterface
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
     *
     * @since 3.0.0
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
        $arguments = $this->parameterResolutioner
            ->withRequest($request)
            ->withPriorityResolver(new TypeParameterResolver(ServerRequestInterface::class, $request))
            ->resolveParameters(...$this->getReflection()->getParameters());

        /** @var mixed $response */
        $response = ($this->callback)(...$arguments);

        return $this->responseResolutioner->withRequest($request)->resolveResponse($response);
    }
}
