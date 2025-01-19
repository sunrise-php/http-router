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

use ReflectionClass;
use ReflectionMethod;

use function array_reverse;

/**
 * @since 3.0.0
 */
final class ReflectorHelper
{
    /**
     * @param ReflectionClass<object> $class
     *
     * @return array<array-key, ReflectionClass<object>>
     */
    public static function getClassAncestry(ReflectionClass $class): array
    {
        $ancestry = [$class];
        while ($class = $class->getParentClass()) {
            $ancestry[] = $class;
        }

        return array_reverse($ancestry);
    }

    /**
     * @param ReflectionMethod $method
     *
     * @return array<array-key, ReflectionClass<object>|ReflectionMethod>
     */
    public static function getMethodAncestry(ReflectionMethod $method): array
    {
        $ancestry = self::getClassAncestry($method->getDeclaringClass());

        $ancestry[] = $method;

        return $ancestry;
    }

    /**
     * @param ReflectionClass<object>|ReflectionMethod $classOrMethod
     *
     * @return array<array-key, ReflectionClass<object>|ReflectionMethod>
     */
    public static function getClassOrMethodAncestry(ReflectionClass|ReflectionMethod $classOrMethod): array
    {
        return $classOrMethod instanceof ReflectionClass
            ? self::getClassAncestry($classOrMethod)
            : self::getMethodAncestry($classOrMethod);
    }
}
