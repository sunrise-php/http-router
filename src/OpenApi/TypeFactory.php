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

namespace Sunrise\Http\Router\OpenApi;

use ReflectionNamedType;
use ReflectionType;

/**
 * @since 3.0.0
 */
final class TypeFactory
{
    public static function mixedPhpType(): Type
    {
        return new Type(Type::PHP_TYPE_NAME_MIXED, true);
    }

    public static function fromPhpTypeReflection(?ReflectionType $phpTypeReflection): Type
    {
        if ($phpTypeReflection === null) {
            return self::mixedPhpType();
        }

        if ($phpTypeReflection instanceof ReflectionNamedType) {
            return new Type(
                $phpTypeReflection->getName(),
                $phpTypeReflection->allowsNull(),
            );
        }

        return new Type(
            (string) $phpTypeReflection,
            $phpTypeReflection->allowsNull(),
        );
    }
}
