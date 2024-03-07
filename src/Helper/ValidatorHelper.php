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
use Symfony\Component\Validator\Constraint as ValidatorConstraint;

/**
 * @since 3.0.0
 */
final class ValidatorHelper
{
    /**
     * @return Generator<int, ValidatorConstraint>
     */
    public static function getParameterConstraints(ReflectionParameter $parameter): Generator
    {
        /** @var ReflectionAttribute<RouterConstraint> $annotation */
        foreach ($parameter->getAttributes(RouterConstraint::class) as $annotation) {
            $routerConstraint = $annotation->newInstance();
            if ($routerConstraint->value instanceof ValidatorConstraint) {
                yield $routerConstraint->value;
            }
        }
    }
}
