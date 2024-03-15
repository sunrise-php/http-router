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

use BackedEnum;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Stringable;

use function get_debug_type;
use function is_callable;
use function is_int;
use function is_string;
use function sprintf;

/**
 * @since 3.0.0
 */
final class Stringifier
{
    /**
     * @throws InvalidArgumentException
     */
    public static function stringifyValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }
        if ($value instanceof Stringable) {
            return $value->__toString();
        }

        throw new InvalidArgumentException(sprintf(
            'The %s value could not be converted to a string; ' .
            'supported types: string, integer, backed enum and stringable object.',
            get_debug_type($value),
        ));
    }

    public static function stringifyReference(mixed $reference): string
    {
        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_callable($reference, true, $result)) {
            return $result;
        }

        return get_debug_type($reference);
    }

    public static function stringifyParameter(ReflectionParameter $parameter): string
    {
        $function = $parameter->getDeclaringFunction();

        if ($function instanceof ReflectionMethod) {
            return sprintf('%s::%s($%s[%d])', $function->getDeclaringClass()->getName(), $function->getName(), $parameter->getName(), $parameter->getPosition());
        }

        return sprintf('%s($%s[%d])', $function->getName(), $parameter->getName(), $parameter->getPosition());
    }

    public static function stringifyResponder(ReflectionMethod|ReflectionFunction $responder): string
    {
        if ($responder instanceof ReflectionMethod) {
            return sprintf('%s::%s()', $responder->getDeclaringClass()->getName(), $responder->getName());
        }

        return sprintf('%s()', $responder->getName());
    }
}
