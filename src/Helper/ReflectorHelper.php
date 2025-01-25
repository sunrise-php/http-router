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
     * @param ReflectionClass<object>|ReflectionMethod $proband
     *
     * @return array<array-key, ReflectionClass<object>|ReflectionMethod>
     */
    public static function getAncestry(ReflectionClass|ReflectionMethod $proband): array
    {
        return $proband instanceof ReflectionClass
            ? self::getClassAncestry($proband)
            : self::getMethodAncestry($proband);
    }

    /**
     * @param ReflectionClass<object>|ReflectionMethod $proband
     * @param class-string<T> $annotationName
     *
     * @return Generator<array-key, T>
     *
     * @template T of object
     */
    public static function getAncestralAnnotations(
        ReflectionClass|ReflectionMethod $proband,
        string $annotationName,
    ): Generator {
        foreach (self::getAncestry($proband) as $member) {
            /** @var list<ReflectionAttribute<T>> $annotations */
            $annotations = $member->getAttributes($annotationName, ReflectionAttribute::IS_INSTANCEOF);
            foreach ($annotations as $annotation) {
                yield $annotation->newInstance();
            }
        }
    }
}
