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
use Sunrise\Http\Router\Exception\InvalidReferenceException;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Closure;
use ReflectionClass;

/**
 * Import functions
 */
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
class ReferenceResolver implements ReferenceResolverInterface
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
        $this->parameterResolutioner->setContainer($container);
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
            return new CallableRequestHandler(
                $reference,
                $this->parameterResolutioner,
                $this->responseResolutioner
            );
        }

        list($class, $method) = $this->normalizeReference($reference);

        if (isset($class) && isset($method) && method_exists($class, $method)) {
            return new CallableRequestHandler(
                [$this->resolveClass($class), $method],
                $this->parameterResolutioner,
                $this->responseResolutioner
            );
        }

        if (!isset($method) && isset($class) && is_subclass_of($class, RequestHandlerInterface::class)) {
            return $this->resolveClass($class);
        }

        throw new InvalidReferenceException(sprintf(
            'Unable to resolve the "%s" reference to a request handler.',
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
            return new CallableMiddleware(
                $reference,
                $this->parameterResolutioner,
                $this->responseResolutioner
            );
        }

        list($class, $method) = $this->normalizeReference($reference);

        if (isset($class) && isset($method) && method_exists($class, $method)) {
            return new CallableMiddleware(
                [$this->resolveClass($class), $method],
                $this->parameterResolutioner,
                $this->responseResolutioner
            );
        }

        if (!isset($method) && isset($class) && is_subclass_of($class, MiddlewareInterface::class)) {
            return $this->resolveClass($class);
        }

        throw new InvalidReferenceException(sprintf(
            'Unable to resolve the "%s" reference to a middleware.',
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
     * Normalizes the given reference
     *
     * @param mixed $reference
     *
     * @return array{0: ?class-string, 1: ?non-empty-string}
     */
    private function normalizeReference($reference): array
    {
        if (is_array($reference) && is_callable($reference, true)) {
            /** @var array{0: class-string, 1: non-empty-string} $reference */
            return $reference;
        }

        if (is_string($reference)) {
            /** @var class-string $reference */
            return [$reference, null];
        }

        return [null, null];
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
        $reference = $this->normalizeReference($reference);

        if (isset($reference[0], $reference[1])) {
            return $reference[0] . '@' . $reference[1];
        }

        if (isset($reference[0])) {
            return $reference[0];
        }

        return '';
    }

    /**
     * Resolves the given class
     *
     * @param class-string<T> $className
     *
     * @return T
     *
     * @template T
     */
    private function resolveClass(string $className)
    {
        if (isset($this->container) && $this->container->has($className)) {
            /** @var T */
            return $this->container->get($className);
        }

        $reflection = new ReflectionClass($className);
        if (!$reflection->isInstantiable()) {
            throw new ReferenceResolvingException();
        }

        $arguments = [];
        $constructor = $reflection->getConstructor();
        if (isset($constructor)) {
            $arguments = $this->parameterResolutioner->resolveParameters(
                ...$constructor->getParameters()
            );
        }

        return new $className(...$arguments);
    }
}
