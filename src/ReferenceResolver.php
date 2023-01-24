<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\Exception\ResolvingReferenceException;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Closure;
use ReflectionClass;

/**
 * Import functions
 */
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
     * The resolver's parameter resolutioner
     *
     * @var ParameterResolutionerInterface
     */
    private ParameterResolutionerInterface $parameterResolutioner;
    /**
     * The resolver's response resolutioner
     *
     * @var ResponseResolutionerInterface
     */
    private ResponseResolutionerInterface $responseResolutioner;

    /**
     * The resolver's container
     *
     * @var ContainerInterface|null
     */
    private ?ContainerInterface $container = null;

    /**
     * Constructor of the class
     *
     * @param ParameterResolutionerInterface|null $parameterResolutioner
     * @param ResponseResolutionerInterface|null $responseResolutioner
     */
    public function __construct(
        ?ParameterResolutionerInterface $parameterResolutioner = null,
        ?ResponseResolutionerInterface $responseResolutioner = null
    ) {
        $this->parameterResolutioner = $parameterResolutioner ?? new ParameterResolutioner();
        $this->responseResolutioner = $responseResolutioner ?? new ResponseResolutioner();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(?ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function addParameterResolver(ParameterResolverInterface ...$resolvers): void
    {
        $this->parameterResolutioner->addResolver(...$resolvers);
    }

    /**
     * {@inheritdoc}
     */
    public function addResponseResolver(ResponseResolverInterface ...$resolvers): void
    {
        $this->responseResolutioner->addResolver(...$resolvers);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveRequestHandler($reference): RequestHandlerInterface
    {
        if ($reference instanceof RequestHandlerInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallableRequestHandler($reference, $this->parameterResolutioner, $this->responseResolutioner);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        /** @psalm-suppress MixedArgument */
        if (is_array($reference) && is_callable($reference, true) && method_exists($reference[0], $reference[1])) {
            /** @var array{0: class-string|object, 1: non-empty-string} $reference */

            $callback = [is_string($reference[0]) ? $this->resolveClass($reference[0]) : $reference[0], $reference[1]];

            return new CallableRequestHandler($callback, $this->parameterResolutioner, $this->responseResolutioner);
        }

        if (is_string($reference) && is_subclass_of($reference, RequestHandlerInterface::class)) {
            /** @var RequestHandlerInterface */
            return $this->resolveClass($reference);
        }

        throw new ResolvingReferenceException(sprintf(
            'Unable to resolve the reference {%s}',
            $this->stringifyReference($reference)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function resolveMiddleware($reference): MiddlewareInterface
    {
        if ($reference instanceof MiddlewareInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallableMiddleware($reference, $this->parameterResolutioner, $this->responseResolutioner);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        /** @psalm-suppress MixedArgument */
        if (is_array($reference) && is_callable($reference, true) && method_exists($reference[0], $reference[1])) {
            /** @var array{0: class-string|object, 1: non-empty-string} $reference */

            $callback = [is_string($reference[0]) ? $this->resolveClass($reference[0]) : $reference[0], $reference[1]];

            return new CallableMiddleware($callback, $this->parameterResolutioner, $this->responseResolutioner);
        }

        if (is_string($reference) && is_subclass_of($reference, MiddlewareInterface::class)) {
            /** @var MiddlewareInterface */
            return $this->resolveClass($reference);
        }

        throw new ResolvingReferenceException(sprintf(
            'Unable to resolve the reference {%s}',
            $this->stringifyReference($reference)
        ));
    }

    /**
     * {@inheritdoc}
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
     * Resolves the given class
     *
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws LogicException
     *         If the class cannot be directly initialized.
     *
     * @template T
     */
    private function resolveClass(string $class): object
    {
        if (isset($this->container) && $this->container->has($class)) {
            /** @var T */
            return $this->container->get($class);
        }

        $reflection = new ReflectionClass($class);
        if (!$reflection->isInstantiable()) {
            throw new LogicException(sprintf(
                'The class %s cannot be initialized',
                $class
            ));
        }

        $arguments = [];
        $constructor = $reflection->getConstructor();
        if (isset($constructor)) {
            $arguments = $this->parameterResolutioner
                ->resolveParameters(...$constructor->getParameters());
        }

        return $reflection->newInstance(...$arguments);
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
        if (is_array($reference) && is_callable($reference, true, $refString)) {
            return $refString;
        }

        if (is_string($reference)) {
            return $reference;
        }

        return get_debug_type($reference);
    }
}
