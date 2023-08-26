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

use Closure;
use Generator;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\Middleware\CallbackMiddleware;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutionerInterface;
use Sunrise\Http\Router\RequestHandler\CallbackRequestHandler;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutionerInterface;

use function class_exists;
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
     * @var ParameterResolutionerInterface
     */
    private ParameterResolutionerInterface $parameterResolutioner;

    /**
     * @var ResponseResolutionerInterface
     */
    private ResponseResolutionerInterface $responseResolutioner;

    /**
     * @var ClassResolverInterface
     */
    private ClassResolverInterface $classResolver;

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

        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;
        $this->classResolver = $classResolver;
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the reference couldn't be resolved.
     */
    public function resolveRequestHandler(mixed $reference): RequestHandlerInterface
    {
        if ($reference instanceof RequestHandlerInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallbackRequestHandler(
                $reference,
                $this->parameterResolutioner,
                $this->responseResolutioner,
            );
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($reference) && is_callable($reference, true)) {
            /** @var array{0: class-string|object, 1: non-empty-string} $reference */

            if (is_string($reference[0])) {
                $reference[0] = $this->classResolver->resolveClass($reference[0]);
            }

            if (is_callable($reference)) {
                return new CallbackRequestHandler(
                    $reference,
                    $this->parameterResolutioner,
                    $this->responseResolutioner,
                );
            }
        }

        if (is_string($reference) && class_exists($reference)) {
            if (is_subclass_of($reference, RequestHandlerInterface::class)) {
                /** @var RequestHandlerInterface */
                return $this->classResolver->resolveClass($reference);
            }

            if (method_exists($reference, '__invoke')) {
                return new CallbackRequestHandler(
                    $this->classResolver->resolveClass($reference),
                    $this->parameterResolutioner,
                    $this->responseResolutioner,
                );
            }
        }

        throw new LogicException(sprintf(
            'Unable to resolve the reference {%s}',
            self::stringifyReference($reference),
        ));
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the reference couldn't be resolved.
     */
    public function resolveMiddleware(mixed $reference): MiddlewareInterface
    {
        if ($reference instanceof MiddlewareInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallbackMiddleware(
                $reference,
                $this->parameterResolutioner,
                $this->responseResolutioner,
            );
        }

        if (is_string($reference) && class_exists($reference)) {
            if (is_subclass_of($reference, MiddlewareInterface::class)) {
                /** @var MiddlewareInterface */
                return $this->classResolver->resolveClass($reference);
            }

            if (method_exists($reference, '__invoke')) {
                return new CallbackMiddleware(
                    $this->classResolver->resolveClass($reference),
                    $this->parameterResolutioner,
                    $this->responseResolutioner,
                );
            }
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($reference) && is_callable($reference, true)) {
            /** @var array{0: class-string|object, 1: non-empty-string} $reference */

            if (is_string($reference[0])) {
                $reference[0] = $this->classResolver->resolveClass($reference[0]);
            }

            if (is_callable($reference)) {
                return new CallbackMiddleware(
                    $reference,
                    $this->parameterResolutioner,
                    $this->responseResolutioner,
                );
            }
        }

        throw new LogicException(sprintf(
            'Unable to resolve the reference {%s}',
            self::stringifyReference($reference),
        ));
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If one of the references couldn't be resolved.
     */
    public function resolveMiddlewares(array $references): Generator
    {
        /** @psalm-suppress MixedAssignment */
        foreach ($references as $reference) {
            yield $this->resolveMiddleware($reference);
        }
    }

    /**
     * Stringifies the given reference
     *
     * @param mixed $reference
     *
     * @return string
     */
    public static function stringifyReference(mixed $reference): string
    {
        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_callable($reference, true, $result)) {
            return $result;
        }

        return get_debug_type($reference);
    }
}
