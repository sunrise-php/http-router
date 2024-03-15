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

use function ltrim;
use function preg_replace;

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

    public static function adaptPropertyPath(string $propertyPath): string
    {
        $propertyPath = preg_replace('/\x5b([^\x5b\x5d]+)\x5d/', '.$1', $propertyPath);

        return ltrim($propertyPath, '.');
    }
}
