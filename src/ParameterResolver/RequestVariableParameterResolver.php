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

namespace Sunrise\Http\Router\ParameterResolver;

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestVariable;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\Exception\InvalidParameterException;
use Sunrise\Http\Router\Helper\RouteSimplifier;
use Sunrise\Http\Router\Helper\ValidatorHelper;
use Sunrise\Http\Router\ParameterResolver;
use Sunrise\Http\Router\ServerRequest;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolationProxy;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorConstraintViolationProxy;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_map;
use function sprintf;

/**
 * @since 3.0.0
 */
final class RequestVariableParameterResolver implements ParameterResolverInterface
{
    public function __construct(
        private readonly HydratorInterface $hydrator,
        private readonly ?ValidatorInterface $validator = null,
        private readonly ?int $defaultErrorStatusCode = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws HttpException
     * @throws InvalidParameterException
     */
    public function resolveParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): Generator
    {
        if ($request === null) {
            return;
        }

        /** @var list<ReflectionAttribute<RequestVariable>> $annotations */
        $annotations = $parameter->getAttributes(RequestVariable::class);
        if ($annotations === []) {
            return;
        }

        $route = ServerRequest::create($request)->getRoute();
        $processParams = $annotations[0]->newInstance();

        $variableName = $processParams->variableName ?? $parameter->getName();
        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;

        if (!$route->hasAttribute($variableName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            }

            throw new InvalidParameterException(sprintf(
                'The parameter %s expects a value of the variable {%s} from the route %s ' .
                'which is not present in the request, most likely, because the variable is optional. ' .
                'To resolve this issue, assign the default value to the parameter.',
                ParameterResolver::stringifyParameter($parameter),
                $variableName,
                $route->getName(),
            ));
        }

        try {
            $argument = $this->hydrator->castValue($route->getAttribute($variableName), Type::fromParameter($parameter), path: [$variableName]);
        } catch (InvalidValueException $e) {
            throw HttpExceptionFactory::invalidVariable($processParams->errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder('{{ variable_name }}', $variableName)
                ->addMessagePlaceholder('{{ route_uri }}', RouteSimplifier::simplifyRoute($route->getPath()))
                ->addConstraintViolation(HydratorConstraintViolationProxy::create($e));
        } catch (InvalidDataException $e) {
            throw HttpExceptionFactory::invalidVariable($processParams->errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder('{{ variable_name }}', $variableName)
                ->addMessagePlaceholder('{{ route_uri }}', RouteSimplifier::simplifyRoute($route->getPath()))
                ->addConstraintViolation(...array_map(HydratorConstraintViolationProxy::create(...), $e->getExceptions()));
        }

        if (isset($this->validator)) {
            if (($constraints = ValidatorHelper::getParameterConstraints($parameter))->valid()) {
                if (($violations = $this->validator->validate($argument, [...$constraints]))->count() > 0) {
                    throw HttpExceptionFactory::invalidVariable($processParams->errorMessage, $errorStatusCode)
                        ->addMessagePlaceholder('{{ variable_name }}', $variableName)
                        ->addMessagePlaceholder('{{ route_uri }}', RouteSimplifier::simplifyRoute($route->getPath()))
                        ->addConstraintViolation(...array_map(ValidatorConstraintViolationProxy::create(...), [...$violations]));
                }
            }
        }

        yield $argument;
    }

    public function getWeight(): int
    {
        return 100;
    }
}