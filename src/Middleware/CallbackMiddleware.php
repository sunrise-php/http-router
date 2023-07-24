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
use Sunrise\Http\Router\ParameterResolutionerInterface;
use Sunrise\Http\Router\ParameterResolver\TypeParameterResolver;
use Sunrise\Http\Router\ResponseResolutionerInterface;

use function Sunrise\Http\Router\reflect_callable;

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
        $args = $this->parameterResolutioner
            ->withRequest($request)
            ->withPriorityResolver(
                new TypeParameterResolver(ServerRequestInterface::class, $request),
                new TypeParameterResolver(RequestHandlerInterface::class, $handler),
            )
            ->resolveParameters(...reflect_callable($this->callback)->getParameters());

        /** @var mixed $response */
        $response = ($this->callback)(...$args);

        return $this->responseResolutioner->withRequest($request)->resolveResponse($response);
    }
}
