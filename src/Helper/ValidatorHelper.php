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

namespace Sunrise\Http\Router\Helper;

use Generator;
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\Constraint as RouterConstraint;
use Sunrise\Http\Router\ConstraintViolation as RouterConstraintViolation;
use Sunrise\Http\Router\ConstraintViolationInterface as RouterConstraintViolationInterface;
use Symfony\Component\Validator\Constraint as ValidatorConstraint;
use Symfony\Component\Validator\ConstraintViolationInterface as ValidatorConstraintViolationInterface;

/**
 * @since 3.0.0
 */
final class ValidatorHelper
{
    /**
     * @return list<ValidatorConstraint>
     */
    public static function getParameterValidatorConstraints(ReflectionParameter $parameter): array
    {
        $validatorConstraints = [];
        /** @var ReflectionAttribute<RouterConstraint> $annotation */
        foreach ($parameter->getAttributes(RouterConstraint::class) as $annotation) {
            $routerConstraint = $annotation->newInstance();
            if ($routerConstraint->value instanceof ValidatorConstraint) {
                $validatorConstraints[] = $routerConstraint->value;
            }
        }

        return $validatorConstraints;
    }

    /**
     * @return Generator<int, RouterConstraintViolationInterface>
     */
    public static function adaptValidatorConstraintViolations(ValidatorConstraintViolationInterface ...$validatorConstraintViolations): Generator
    {
        foreach ($validatorConstraintViolations as $validatorConstraintViolation) {
            yield new RouterConstraintViolation(
                (string) $validatorConstraintViolation->getMessage(),
                $validatorConstraintViolation->getMessageTemplate(),
                $validatorConstraintViolation->getParameters(),
                $validatorConstraintViolation->getPropertyPath(),
                $validatorConstraintViolation->getCode(),
            );
        }
    }
}
