<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Middleware;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\ParameterResolver\KnownTypeParameterResolver;
use Sunrise\Http\Router\ParameterResolutionerInterface;
use Sunrise\Http\Router\ResponseResolutionerInterface;
use ReflectionFunctionAbstract;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Import functions
 */
use function Sunrise\Http\Router\reflect_callable;

/**
 * CallableMiddleware
 *
 * @since 2.8.0
 */
final class CallableMiddleware implements MiddlewareInterface
{

    /**
     * The middleware callback
     *
     * @var callable
     */
    private $callback;

    /**
     * The callback's parameter resolutioner
     *
     * @var ParameterResolutionerInterface
     */
    private ParameterResolutionerInterface $parameterResolutioner;

    /**
     * The callback's response resolutioner
     *
     * @var ResponseResolutionerInterface
     */
    private ResponseResolutionerInterface $responseResolutioner;

    /**
     * Constructor of the class
     *
     * @param callable $callback
     * @param ParameterResolutionerInterface $parameterResolutioner
     * @param ResponseResolutionerInterface $responseResolutioner
     */
    public function __construct(
        callable $callback,
        ParameterResolutionerInterface $parameterResolutioner,
        ResponseResolutionerInterface $responseResolutioner
    ) {
        $this->callback = $callback;
        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;
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
        return reflect_callable($this->callback);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $resolvers = [
            new KnownTypeParameterResolver(ServerRequestInterface::class, $request),
            new KnownTypeParameterResolver(RequestHandlerInterface::class, $handler),
        ];

        $arguments = $this->parameterResolutioner
            ->withContext($request)
            ->withPriorityResolver(...$resolvers)
            ->resolveParameters(...$this->getReflection()->getParameters());

        /** @var mixed */
        $response = ($this->callback)(...$arguments);

        return $this->responseResolutioner
            ->withContext($request)
            ->resolveResponse($response);
    }
}
