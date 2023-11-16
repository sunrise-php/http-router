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
use ReflectionClass;
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
     * @var array<class-string, object>
     */
    private array $resolvedClasses = [];

    /**
     * Constructor of the class
     *
     * @param ParameterResolutionerInterface $parameterResolutioner
     * @param ResponseResolutionerInterface $responseResolutioner
     */
    public function __construct(
        ParameterResolutionerInterface $parameterResolutioner,
        ResponseResolutionerInterface $responseResolutioner,
    ) {
        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;
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
            return new CallbackRequestHandler($reference, $this->parameterResolutioner, $this->responseResolutioner);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($reference) && is_callable($reference, true)) {
            /** @var array{0: class-string|object, 1: non-empty-string} $reference */

            if (is_string($reference[0])) {
                $reference[0] = $this->resolveClass($reference[0]);
            }

            if (is_callable($reference)) {
                // phpcs:ignore Generic.Files.LineLength
                return new CallbackRequestHandler($reference, $this->parameterResolutioner, $this->responseResolutioner);
            }
        }

        if (is_string($reference) && is_subclass_of($reference, RequestHandlerInterface::class)) {
            return $this->resolveClass($reference);
        }

        throw new LogicException(sprintf(
            'The reference {%s} cannot be resolved.',
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
            return new CallbackMiddleware($reference, $this->parameterResolutioner, $this->responseResolutioner);
        }

        if (is_string($reference) && is_subclass_of($reference, MiddlewareInterface::class)) {
            return $this->resolveClass($reference);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($reference) && is_callable($reference, true)) {
            /** @var array{0: class-string|object, 1: non-empty-string} $reference */

            if (is_string($reference[0])) {
                $reference[0] = $this->resolveClass($reference[0]);
            }

            if (is_callable($reference)) {
                return new CallbackMiddleware($reference, $this->parameterResolutioner, $this->responseResolutioner);
            }
        }

        throw new LogicException(sprintf(
            'The reference {%s} cannot be resolved.',
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
     * Resolves the given named class
     *
     * @param class-string<T> $fqn
     *
     * @return T
     *
     * @template T of object
     *
     * @throws LogicException If the class couldn't be resolved.
     */
    private function resolveClass(string $fqn): object
    {
        if (isset($this->resolvedClasses[$fqn])) {
            /** @var T */
            return $this->resolvedClasses[$fqn];
        }

        if (!class_exists($fqn)) {
            throw new LogicException(sprintf('The class {%s} does not exist.', $fqn));
        }

        $class = new ReflectionClass($fqn);
        if (!$class->isInstantiable()) {
            throw new LogicException(sprintf('The class {%s} is not instantiable.', $fqn));
        }

        $arguments = [];
        $constructor = $class->getConstructor();
        if ($constructor?->getNumberOfParameters()) {
            $arguments = $this->parameterResolutioner->resolveParameters(...$constructor->getParameters());
        }

        /** @var T */
        return $this->resolvedClasses[$fqn] = $class->newInstance(...$arguments);
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
