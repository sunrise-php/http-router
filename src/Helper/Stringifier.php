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

use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

use function sprintf;

/**
 * @since 3.0.0
 */
final class Stringifier
{
    public static function stringifyParameter(ReflectionParameter $parameter): string
    {
        $function = $parameter->getDeclaringFunction();

        if ($function instanceof ReflectionMethod) {
            return sprintf(
                '%s::%s($%s[%d])',
                $function->getDeclaringClass()->getName(),
                $function->getName(),
                $parameter->getName(),
                $parameter->getPosition(),
            );
        }

        return sprintf(
            '%s($%s[%d])',
            $function->getName(),
            $parameter->getName(),
            $parameter->getPosition(),
        );
    }

    public static function stringifyFunction(ReflectionMethod|ReflectionFunction $function): string
    {
        if ($function instanceof ReflectionMethod) {
            return sprintf(
                '%s::%s()',
                $function->getDeclaringClass()->getName(),
                $function->getName(),
            );
        }

        return sprintf(
            '%s()',
            $function->getName(),
        );
    }
}
