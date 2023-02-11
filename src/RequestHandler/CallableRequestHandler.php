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
use Sunrise\Http\Router\ParameterResolver\KnownTypedParameterResolver;
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $arguments = $this->parameterResolutioner
            ->withContext($request)
            ->withPriorityResolver(new KnownTypedParameterResolver(ServerRequestInterface::class, $request))
            ->resolveParameters(...$this->getReflection()->getParameters());

        /** @var mixed */
        $response = ($this->callback)(...$arguments);

        return $this->responseResolutioner
            ->withContext($request)
            ->resolveResponse($response);
    }
}
