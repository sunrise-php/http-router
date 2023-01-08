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
     * The reference resolver container
     *
     * @var ContainerInterface|null
     */
    private ?ContainerInterface $container = null;

    /**
     * The reference resolver's response resolver
     *
     * @var ResponseResolverInterface|null
     */
    private ?ResponseResolverInterface $responseResolver = null;

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
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
    public function getResponseResolver(): ?ResponseResolverInterface
    {
        return $this->responseResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseResolver(?ResponseResolverInterface $responseResolver): void
    {
        $this->responseResolver = $responseResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function toRequestHandler($reference): RequestHandlerInterface
    {
        if ($reference instanceof RequestHandlerInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallableRequestHandler($reference, $this->responseResolver);
        }

        list($class, $method) = $this->normalizeReference($reference);

        if (isset($class) && isset($method) && method_exists($class, $method)) {
            /** @var callable */
            $callback = [$this->resolveClass($class), $method];

            return new CallableRequestHandler($callback, $this->responseResolver);
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
    public function toMiddleware($reference): MiddlewareInterface
    {
        if ($reference instanceof MiddlewareInterface) {
            return $reference;
        }

        if ($reference instanceof Closure) {
            return new CallableMiddleware($reference);
        }

        list($class, $method) = $this->normalizeReference($reference);

        if (isset($class) && isset($method) && method_exists($class, $method)) {
            return new CallableMiddleware([$this->resolveClass($class), $method]);
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
    public function toMiddlewares(array $references): array
    {
        $middlewares = [];
        /** @psalm-suppress MixedAssignment */
        foreach ($references as $reference) {
            $middlewares[] = $this->toMiddleware($reference);
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

        /** @psalm-suppress MixedMethodCall */
        return new $className;
    }
}
