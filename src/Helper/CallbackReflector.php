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

use Closure;
use LogicException;
use ReflectionFunction;
use ReflectionMethod;

use function function_exists;
use function is_array;
use function is_object;
use function is_string;
use function method_exists;
use function str_contains;

/**
 * Internal callback reflector
 *
 * @internal
 */
final class CallbackReflector
{

    /**
     * Tries to reflect the given callback
     *
     * @param callable $callback
     *
     * @return ReflectionFunction|ReflectionMethod
     *
     * @throws LogicException If the callback couldn't be reflected.
     */
    public static function reflectCallback(callable $callback): ReflectionFunction|ReflectionMethod
    {
        if ($callback instanceof Closure) {
            return new ReflectionFunction($callback);
        }

        if (is_array($callback)) {
            /** @psalm-suppress MixedArgument */
            return new ReflectionMethod(...$callback);
        }

        if (is_object($callback) && method_exists($callback, '__invoke')) {
            return new ReflectionMethod($callback, '__invoke');
        }

        if (is_string($callback) && str_contains($callback, '::')) {
            return new ReflectionMethod($callback);
        }

        if (is_string($callback) && function_exists($callback)) {
            return new ReflectionFunction($callback);
        }

        throw new LogicException('Unsupported callback');
    }
}
