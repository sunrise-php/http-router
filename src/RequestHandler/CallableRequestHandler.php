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
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\ResponseResolverInterface;
use ReflectionFunctionAbstract;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Import functions
 */
use function Sunrise\Http\Router\reflect_callable;

/**
 * CallableRequestHandler
 */
final class CallableRequestHandler implements RequestHandlerInterface
{

    /**
     * The handler callback
     *
     * @var callable
     */
    private $callback;

    /**
     * The callback's parameter resolver
     *
     * @var ParameterResolverInterface
     */
    private ParameterResolverInterface $parameterResolver;

    /**
     * The callback's response resolver
     *
     * @var ResponseResolverInterface
     */
    private ResponseResolverInterface $responseResolver;

    /**
     * The callback's reflection
     *
     * @var ReflectionFunction|ReflectionMethod
     */
    private ?ReflectionFunctionAbstract $reflection = null;

    /**
     * Constructor of the class
     *
     * @param callable $callback
     * @param ParameterResolverInterface $parameterResolver
     * @param ResponseResolverInterface $responseResolver
     */
    public function __construct(
        callable $callback,
        ParameterResolverInterface $parameterResolver,
        ResponseResolverInterface $responseResolver
    ) {
        $this->callback = $callback;
        $this->parameterResolver = $parameterResolver;
        $this->responseResolver = $responseResolver;
    }

    /**
     * Gets the callback's reflection
     *
     * @return ReflectionFunction|ReflectionMethod
     *
     * @since 3.0.0
     */
    public function getReflection(): ReflectionFunctionAbstract
    {
        return $this->reflection ??= reflect_callable($this->callback);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $arguments = $this->parameterResolver
            ->withNames($request->getAttributes())
            ->withType(ServerRequestInterface::class, $request)
            ->resolveParameters(...$this->getReflection()->getParameters());

        return $this->responseResolver->resolveResponse(($this->callback)(...$arguments));
    }
}
