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
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestPathVariable;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Helper\HydratorHelper;
use Sunrise\Http\Router\Helper\ValidatorHelper;
use Sunrise\Http\Router\ParameterResolver;
use Sunrise\Http\Router\Route;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function count;
use function sprintf;

/**
 * @since 3.0.0
 */
final class RequestPathVariableParameterResolver implements ParameterResolverInterface
{
    public function __construct(
        private readonly HydratorInterface $hydrator,
        private readonly ?ValidatorInterface $validator = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     *
     * @throws HttpException If the request's path variable isn't valid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestPathVariable>> $annotations */
        $annotations = $parameter->getAttributes(RequestPathVariable::class);
        if ($annotations === []) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        $route = $context->getAttribute('@route');
        if (! $route instanceof Route) {
            throw new LogicException(sprintf(
                'The #[RequestPathVariable] annotation cannot be applied to the parameter %s ' .
                'because the request does not contain information about the requested route.',
                ParameterResolver::stringifyParameter($parameter),
            ));
        }

        $requestVariable = $annotations[0]->newInstance();

        $variableName = $requestVariable->name ?? $parameter->getName();

        if (!$route->hasAttribute($variableName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw new LogicException(sprintf(
                'The parameter %1$s expects the value of the variable %3$s from the route %2$s ' .
                'which is not present in the request, most likely, because the variable is optional. ' .
                'To resolve this issue, assign the default value to the parameter or make it nullable.',
                ParameterResolver::stringifyParameter($parameter),
                $route->getName(),
                $variableName,
            ));
        }

        $placeholders = [
            '{{ variable_name }}' => $variableName,
            '{{ route_path }}' => $route->getPath(),
        ];

        try {
            $argument = $this->hydrator->castValue($route->getAttribute($variableName), Type::fromParameter($parameter), path: [$variableName]);
        } catch (InvalidDataException|InvalidValueException $e) {
            throw HttpException::pathVariableInvalid($requestVariable->errorStatusCode, $requestVariable->errorMessage, $placeholders, previous: $e)
                ->addConstraintViolation(...HydratorHelper::adaptConstraintViolations($e));
        }

        if (isset($this->validator)) {
            if (count($constraints = ValidatorHelper::getParameterConstraints($parameter)) > 0) {
                if (count($violations = $this->validator->validate($argument, $constraints)) > 0) {
                    throw HttpException::pathVariableInvalid($requestVariable->errorStatusCode, $requestVariable->errorMessage, $placeholders)
                        ->addConstraintViolation(...ValidatorHelper::adaptConstraintViolations(...$violations));
                }
            }
        }

        yield $argument;
    }
}
