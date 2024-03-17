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

namespace Sunrise\Http\Router\Validation\Constraint;

use ReflectionAttribute;
use Sunrise\Http\Router\Annotation\Constraint as ParameterConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @since 3.0.0
 */
final class ArgumentConstraintValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     *
     * @throws UnexpectedTypeException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof ArgumentConstraint) {
            throw new UnexpectedTypeException($constraint, ArgumentConstraint::class);
        }

        $constraints = [];
        /** @var list<ReflectionAttribute<ParameterConstraint>> $parameterAnnotations */
        $parameterAnnotations = $constraint->getParameter()->getAttributes(ParameterConstraint::class);
        foreach ($parameterAnnotations as $parameterAnnotation) {
            $parameterConstraint = $parameterAnnotation->newInstance();
            if ($parameterConstraint->value instanceof Constraint) {
                $constraints[] = $parameterConstraint->value;
            }
        }

        if ($constraints === []) {
            return;
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, $constraints);
    }
}
