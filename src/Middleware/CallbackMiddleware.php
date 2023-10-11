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
use Sunrise\Http\Router\Helper\CallbackReflector;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutionerInterface;
use Sunrise\Http\Router\ParameterResolving\ParameterResolver\ObjectInjectionParameterResolver;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutionerInterface;

/**
 * CallbackMiddleware
 *
 * @since 3.0.0
 */
final class CallbackMiddleware implements MiddlewareInterface
{

    /**
     * The middleware's callback
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
        ResponseResolutionerInterface $responseResolutioner,
    ) {
        $this->callback = $callback;
        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $responder = CallbackReflector::reflectCallback($this->callback);

        $arguments = $this->parameterResolutioner
            ->withContext($request)
            ->withPriorityResolver(
                new ObjectInjectionParameterResolver($request),
                new ObjectInjectionParameterResolver($handler),
            )
            ->resolveParameters(...$responder->getParameters());

        /** @var mixed $response */
        $response = ($this->callback)(...$arguments);

        return $this->responseResolutioner->resolveResponse($request, $response, $responder);
    }
}
