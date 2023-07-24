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

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\ResolvingReferenceException;
use Sunrise\Http\Router\Middleware\CallbackMiddleware;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Closure;

use function get_debug_type;
use function is_array;
use function is_callable;
use function is_string;
use function is_subclass_of;
use function method_exists;
use function sprintf;

/**
 * ReferenceResolver
 *
 * @since 2.10.0
 */
final class ReferenceResolver implements ReferenceResolverInterface
{

    /**
     * @var ClassResolverInterface
     */
    private ClassResolverInterface $classResolver;

    /**
     * @var ParameterResolutionerInterface
     */
    private ParameterResolutionerInterface $parameterResolutioner;

    /**
     * @var ResponseResolutionerInterface
     */
    private ResponseResolutionerInterface $responseResolutioner;

    /**
     * Constructor of the class
     *
     * @param ParameterResolutionerInterface $parameterResolutioner
     * @param ResponseResolutionerInterface $responseResolutioner
     * @param ClassResolverInterface|null $classResolver
     */
    public function __construct(
        ParameterResolutionerInterface $parameterResolutioner,
        ResponseResolutionerInterface $responseResolutioner,
        ?ClassResolverInterface $classResolver = null
    ) {
        $classResolver ??= new ClassResolver($parameterResolutioner);

        $this->classResolver = $classResolver;
        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;
    }

    /**
     * @inheritDoc
     */
    public function resolveRequestHandler($reference): RequestHandlerInterface
    {
        if ($reference instanceof RequestHandlerInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallableRequestHandler($reference, $this->parameterResolutioner, $this->responseResolutioner);
        }

        if (is_string($reference) && is_subclass_of($reference, RequestHandlerInterface::class)) {
            /** @var RequestHandlerInterface */
            return $this->classResolver->resolveClass($reference);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        /** @psalm-suppress MixedArgument */
        if (is_array($reference) && is_callable($reference, true) && method_exists($reference[0], $reference[1])) {
            /** @var array{0: class-string|object, 1: non-empty-string} $reference */

            if (is_string($reference[0])) {
                $reference[0] = $this->classResolver->resolveClass($reference[0]);
            }

            return new CallableRequestHandler(
                [$reference[0], $reference[1]],
                $this->parameterResolutioner,
                $this->responseResolutioner
            );
        }

        throw new ResolvingReferenceException(sprintf(
            'Unable to resolve the reference {%s}',
            $this->stringifyReference($reference)
        ));
    }

    /**
     * @inheritDoc
     */
    public function resolveMiddleware($reference): MiddlewareInterface
    {
        if ($reference instanceof MiddlewareInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallbackMiddleware($reference, $this->parameterResolutioner, $this->responseResolutioner);
        }

        if (is_string($reference) && is_subclass_of($reference, MiddlewareInterface::class)) {
            /** @var MiddlewareInterface */
            return $this->classResolver->resolveClass($reference);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        /** @psalm-suppress MixedArgument */
        if (is_array($reference) && is_callable($reference, true) && method_exists($reference[0], $reference[1])) {
            /** @var array{0: class-string|object, 1: non-empty-string} $reference */

            if (is_string($reference[0])) {
                $reference[0] = $this->classResolver->resolveClass($reference[0]);
            }

            return new CallbackMiddleware(
                [$reference[0], $reference[1]],
                $this->parameterResolutioner,
                $this->responseResolutioner
            );
        }

        throw new ResolvingReferenceException(sprintf(
            'Unable to resolve the reference {%s}',
            $this->stringifyReference($reference)
        ));
    }

    /**
     * @inheritDoc
     */
    public function resolveMiddlewares(array $references): array
    {
        $middlewares = [];
        /** @psalm-suppress MixedAssignment */
        foreach ($references as $reference) {
            $middlewares[] = $this->resolveMiddleware($reference);
        }

        return $middlewares;
    }

    /**
     * Stringifies the given reference
     *
     * @param mixed $reference
     *
     * @return string
     */
    private function stringifyReference($reference): string
    {
        if (is_array($reference) && is_callable($reference, true, $stringReference)) {
            return $stringReference;
        }

        if (is_string($reference)) {
            return $reference;
        }

        return get_debug_type($reference);
    }
}
