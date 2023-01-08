<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\RequestHandler;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\ResponseResolverInterface;
use ReflectionFunctionAbstract;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;

/**
 * Import functions
 */
use function ksort;
use function Sunrise\Http\Router\reflect_callable;

/**
 * CallableRequestHandler
 */
class CallableRequestHandler implements RequestHandlerInterface
{

    /**
     * The handler callback
     *
     * @var callable
     */
    private $callback;

    /**
     * The callback arguments
     *
     * @var list<mixed>
     *
     * @since 3.0.0
     *
     * @psalm-immutable
     */
    private array $arguments = [];

    /**
     * The response resolver
     *
     * @var ResponseResolverInterface|null
     *
     * @since 3.0.0
     */
    private ?ResponseResolverInterface $responseResolver;

    /**
     * Constructor of the class
     *
     * @param callable $callback
     * @param ResponseResolverInterface|null $responseResolver
     */
    public function __construct(callable $callback, ?ResponseResolverInterface $responseResolver = null)
    {
        $this->callback = $callback;
        $this->responseResolver = $responseResolver;
    }

    /**
     * Gets the callback reflection
     *
     * @return ReflectionFunction|ReflectionMethod
     *
     * @since 3.0.0
     */
    public function getReflection(): ReflectionFunctionAbstract
    {
        return reflect_callable($this->callback);
    }

    /**
     * Gets the callback parameters
     *
     * @return list<ReflectionParameter>
     *
     * @since 3.0.0
     */
    public function getParameters(): array
    {
        return $this->getReflection()->getParameters();
    }

    /**
     * Gets the callback's attributed parameter
     *
     * @param class-string $attribute
     *
     * @return ReflectionParameter|null
     *
     * @since 3.0.0
     */
    public function getAttributedParameter(string $attribute): ?ReflectionParameter
    {
        foreach ($this->getParameters() as $parameter) {
            if ($parameter->getAttributes($attribute)) {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * Gets the callback's return type
     *
     * @return ReflectionType|null
     *
     * @since 3.0.0
     */
    public function getReturnType(): ?ReflectionType
    {
        return $this->getReflection()->getReturnType();
    }

    /**
     * Gets the callback arguments
     *
     * @return list<mixed>
     *
     * @since 3.0.0
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Creates a new instance of the request handler with the given arguments and returns it
     *
     * Please note that the first argument will be the server request instance.
     *
     * @param array<int, mixed> $arguments
     *
     * @return static
     *
     * @since 3.0.0
     */
    public function withArguments(array $arguments): self
    {
        ksort($arguments, SORT_NUMERIC);

        $clone = clone $this;
        $clone->arguments = [];

        /** @psalm-suppress MixedAssignment */
        foreach ($arguments as $argument) {
            $clone->arguments[] = $argument;
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = ($this->callback)($request, ...$this->arguments);

        if (isset($this->responseResolver)) {
            return $this->responseResolver->resolveResponse($response);
        }

        return $response;
    }
}
