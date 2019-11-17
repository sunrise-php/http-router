<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import functions
 */
use function is_int;
use function preg_match;

/**
 * Compares the given path and the given subject...
 *
 * @param string $path
 * @param string $subject
 *
 * @return bool
 */
function path_match(string $path, string $subject, &$attributes = null) : bool
{
    $attributes = [];

    $regex = path_regex($path);
    if (!preg_match($regex, $subject, $matches)) {
        return false;
    }

    foreach ($matches as $key => $value) {
        if (!is_int($key) && '' !== $value) {
            $attributes[$key] = $value;
        }
    }

    return true;
}
