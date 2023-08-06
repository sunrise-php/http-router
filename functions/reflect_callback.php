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

namespace Sunrise\Http\Router;

use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;

use function function_exists;
use function is_array;
use function is_object;
use function is_string;
use function method_exists;
use function strpos;

/**
 * Tries to reflect the given callback
 *
 * @param callable $callback
 *
 * @return ReflectionFunction|ReflectionMethod
 *
 * @throws InvalidArgumentException If the given callback cannot be reflected.
 *
 * @since 3.0.0
 */
function reflect_callback(callable $callback): ReflectionFunction|ReflectionMethod
{
    if ($callback instanceof Closure) {
        return new ReflectionFunction($callback);
    }

    if (is_array($callback)) {
        return new ReflectionMethod(...$callback);
    }

    if (is_object($callback) && method_exists($callback, '__invoke')) {
        return new ReflectionMethod($callback, '__invoke');
    }

    if (is_string($callback) && strpos($callback, '::')) {
        return new ReflectionMethod($callback);
    }

    if (is_string($callback) && function_exists($callback)) {
        return new ReflectionFunction($callback);
    }

    throw new InvalidArgumentException('Unsupported callback notation');
}
