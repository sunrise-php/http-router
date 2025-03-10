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

use function get_debug_type;
use function is_array;
use function is_callable;
use function is_string;

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
     * @param array<array-key, ParameterResolverInterface> $parameterResolvers
     * @param array<array-key, ResponseResolverInterface> $responseResolvers
     */
    public static function build(
        array $parameterResolvers = [],
        array $responseResolvers = [],
        ?ContainerInterface $container = null,
    ): ReferenceResolverInterface {
        $parameterResolver = new ParameterResolverChain($parameterResolvers);
        $responseResolver = new ResponseResolverChain($responseResolvers);
        $classResolver = new ClassResolver($parameterResolver, $container);
        $middlewareResolver = new MiddlewareResolver($classResolver, $parameterResolver, $responseResolver);
        $requestHandlerResolver = new RequestHandlerResolver($classResolver, $parameterResolver, $responseResolver);

        return new self($middlewareResolver, $requestHandlerResolver);
    }

    /**
     * @inheritDoc
     */
    public function resolveMiddleware(mixed $reference): MiddlewareInterface
    {
        return $this->middlewareResolver->resolveMiddleware($reference);
    }

    /**
     * @inheritDoc
     */
    public function resolveRequestHandler(mixed $reference): RequestHandlerInterface
    {
        return $this->requestHandlerResolver->resolveRequestHandler($reference);
    }

    public static function stringifyReference(mixed $reference): string
    {
        if (is_string($reference)) {
            return $reference;
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($reference) && is_callable($reference, true, $referenceName)) {
            return $referenceName;
        }

        return get_debug_type($reference);
    }
}
