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

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\ParameterResolver\ParameterResolverInterface;
use Sunrise\Http\Router\ResponseResolver\ResponseResolverInterface;

/**
 * @since 2.10.0
 */
final class ReferenceResolver implements ReferenceResolverInterface
{
    public function __construct(
        private readonly MiddlewareResolverInterface $middlewareResolver,
        private readonly RequestHandlerResolverInterface $requestHandlerResolver,
    ) {
    }

    /**
     * @param ParameterResolverInterface[] $parameterResolvers
     * @param ResponseResolverInterface[] $responseResolvers
     */
    public static function build(
        array $parameterResolvers = [],
        array $responseResolvers = [],
        ContainerInterface|null $container = null,
    ): ReferenceResolverInterface {
        $parameterResolverChain = new ParameterResolverChain($parameterResolvers);
        $responseResolverChain = new ResponseResolverChain($responseResolvers);
        $classResolver = new ClassResolver($parameterResolverChain, $container);
        $middlewareResolver = new MiddlewareResolver($classResolver, $parameterResolverChain, $responseResolverChain);
        $requestHandlerResolver = new RequestHandlerResolver($classResolver, $parameterResolverChain, $responseResolverChain);

        return new self($middlewareResolver, $requestHandlerResolver);
    }

    public function resolveMiddleware(mixed $reference): MiddlewareInterface
    {
        return $this->middlewareResolver->resolveMiddleware($reference);
    }

    public function resolveRequestHandler(mixed $reference): RequestHandlerInterface
    {
        return $this->requestHandlerResolver->resolveRequestHandler($reference);
    }
}
