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
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestVariable;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\Helper\RouteSimplifier;
use Sunrise\Http\Router\ParameterResolverChain;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\ServerRequest;
use Sunrise\Http\Router\Validation\Constraint\ArgumentConstraint;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolationAdapter;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorConstraintViolationAdapter;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
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
    private const PLACEHOLDER_VARIABLE_NAME = '{{ variable_name }}';
    private const PLACEHOLDER_ROUTE_URI = '{{ route_uri }}';

    public function __construct(
        private readonly HydratorInterface $hydrator,
        private readonly ?ValidatorInterface $validator = null,
        private readonly ?int $defaultErrorStatusCode = null,
        private readonly ?string $defaultErrorMessage = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws HttpExceptionInterface
     * @throws InvalidArgumentException
     * @throws InvalidObjectException
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        if (! $context instanceof ServerRequestInterface) {
            return;
        }

        /** @var list<ReflectionAttribute<RequestVariable>> $annotations */
        $annotations = $parameter->getAttributes(RequestVariable::class);
        if ($annotations === []) {
            return;
        }

        $route = ServerRequest::create($context)->getRoute();
        $processParams = $annotations[0]->newInstance();

        $variableName = $processParams->name ?? $parameter->getName();
        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;
        $errorMessage = $processParams->errorMessage ?? $this->defaultErrorMessage;

        if (!$route->hasAttribute($variableName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            }

            throw new InvalidArgumentException(sprintf(
                'The parameter %s expects a value of the variable {%s} from the route %s, ' .
                'which is not present in the request, likely because the variable is optional. ' .
                'To resolve this issue, assign the default value to the parameter.',
                ParameterResolverChain::stringifyParameter($parameter),
                $variableName,
                $route->getName(),
            ));
        }

        try {
            $argument = $this->hydrator->castValue($route->getAttribute($variableName), Type::fromParameter($parameter), path: [$variableName]);
        } catch (InvalidValueException $e) {
            throw HttpExceptionFactory::invalidVariable($errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder(self::PLACEHOLDER_VARIABLE_NAME, $variableName)
                ->addMessagePlaceholder(self::PLACEHOLDER_ROUTE_URI, RouteSimplifier::simplifyRoute($route->getPath()))
                ->addConstraintViolation(HydratorConstraintViolationAdapter::create($e));
        } catch (InvalidDataException $e) {
            throw HttpExceptionFactory::invalidVariable($errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder(self::PLACEHOLDER_VARIABLE_NAME, $variableName)
                ->addMessagePlaceholder(self::PLACEHOLDER_ROUTE_URI, RouteSimplifier::simplifyRoute($route->getPath()))
                ->addConstraintViolation(...array_map(HydratorConstraintViolationAdapter::create(...), $e->getExceptions()));
        }

        if ($this->validator !== null) {
            $violations = $this->validator->startContext()->atPath($variableName)->validate($argument, new ArgumentConstraint($parameter))->getViolations();
            if ($violations->count() > 0) {
                throw HttpExceptionFactory::invalidVariable($errorMessage, $errorStatusCode)
                    ->addMessagePlaceholder(self::PLACEHOLDER_VARIABLE_NAME, $variableName)
                    ->addMessagePlaceholder(self::PLACEHOLDER_ROUTE_URI, RouteSimplifier::simplifyRoute($route->getPath()))
                    ->addConstraintViolation(...array_map(ValidatorConstraintViolationAdapter::create(...), [...$violations]));
            }
        }

        yield $argument;
    }

    public function getWeight(): int
    {
        return 0;
    }
}
