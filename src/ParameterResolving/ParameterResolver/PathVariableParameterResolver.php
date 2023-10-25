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

namespace Sunrise\Http\Router\ParameterResolving\ParameterResolver;

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\PathVariable;
use Sunrise\Http\Router\Dictionary\ErrorSource;
use Sunrise\Http\Router\Exception\Http\HttpNotFoundException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutioner;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;

use function sprintf;

/**
 * PathVariableParameterResolver
 *
 * @since 3.0.0
 */
final class PathVariableParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param HydratorInterface $hydrator
     */
    public function __construct(private HydratorInterface $hydrator)
    {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     *
     * @throws HttpNotFoundException If the request's path variable isn't valid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<PathVariable>> $attributes */
        $attributes = $parameter->getAttributes(PathVariable::class);
        if ($attributes === []) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        $route = $context->getAttribute('@route');
        if (! $route instanceof RouteInterface) {
            throw new LogicException(sprintf(
                'The #[PathVariable] attribute cannot be applied to the parameter {%s}, ' .
                'because the request does not contain information about the requested route, ' .
                'at least at this level of the application.',
                ParameterResolutioner::stringifyParameter($parameter),
            ));
        }

        $attribute = $attributes[0]->newInstance();

        $variableName = $attribute->name ?? $parameter->getName();
        $variableValue = $route->getAttribute($variableName);

        if ($variableValue === null) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw new LogicException(sprintf(
                'The parameter {%1$s} expects the value of the variable {%3$s} from the route "%2$s", ' .
                'which is not present in the request, most likely, because the variable is optional. ' .
                'To resolve this issue, make this parameter nullable or assign it a default value.',
                ParameterResolutioner::stringifyParameter($parameter),
                $route->getName(),
                $variableName,
            ));
        }

        try {
            yield $this->hydrator->castValue(
                $variableValue,
                Type::fromParameter($parameter),
                path: [$variableName],
            );
        } catch (InvalidDataException $e) {
            throw (new HttpNotFoundException(previous: $e))
                ->setSource(ErrorSource::CLIENT_REQUEST_PATH)
                ->addHydratorViolation(...$e->getExceptions());
        } catch (InvalidValueException $e) {
            throw (new HttpNotFoundException(previous: $e))
                ->setSource(ErrorSource::CLIENT_REQUEST_PATH)
                ->addHydratorViolation($e);
        }
    }
}
