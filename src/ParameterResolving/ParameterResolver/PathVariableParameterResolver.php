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
use Sunrise\Http\Router\TypeConversion\TypeConversionerInterface;
use UnexpectedValueException;

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
     * @param TypeConversionerInterface $typeConversioner
     */
    public function __construct(private TypeConversionerInterface $typeConversioner)
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

        if (!$parameter->hasType()) {
            throw new LogicException(sprintf(
                'To use the #[PathVariable] attribute, the parameter {%s} must be typed.',
                ParameterResolutioner::stringifyParameter($parameter),
            ));
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

        $variable = $attributes[0]->newInstance();

        $variableName = $variable->name ?? $parameter->getName();
        /** @var mixed $variableValue */
        $variableValue = $route->getAttribute($variableName);

        if ($variableValue === null) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw new LogicException(sprintf(
                'The parameter {%1$s} expects the value of the variable {%2$s} from the route "%3$s", ' .
                'which is not present in the request, most likely, because the variable is optional. ' .
                'To resolve this issue, make this parameter nullable or assign it a default value.',
                ParameterResolutioner::stringifyParameter($parameter),
                $variableName,
                $route->getName(),
            ));
        }

        try {
            yield $this->typeConversioner->castValue($variableValue, $parameter->getType());
        } catch (UnexpectedValueException $violation) {
            // phpcs:ignore Generic.Files.LineLength
            $message = sprintf('The request cannot be processed with an invalid {%1$s} in the URI path due to: %2$s', $variableName, $violation->getMessage());

            throw (new HttpNotFoundException($message, previous: $violation))
                ->setSource(ErrorSource::CLIENT_REQUEST_PATH);
        }
    }
}
