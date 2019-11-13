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
 * Import classes
 */
use InvalidArgumentException;

/**
 * Import functions
 */
use function preg_match;
use function preg_replace_callback;

/**
 * Converts the given path to URI
 *
 * @param string $path
 * @param array $attrs
 * @param bool $strict
 *
 * @return string
 *
 * @throws InvalidArgumentException
 *
 * @todo Strict mode handle...
 */
function path_uri(string $path, array $attrs = [], bool $strict = false) : string
{
    return preg_replace_callback('/{([0-9A-Za-z_]+)(?:<([^<>]+)>)?}/', function ($matches) use ($attrs, $strict) {
        if (!isset($attrs[$matches[1]])) {
            throw new InvalidArgumentException();
        }

        return $attrs[$matches[1]];
    }, $path);
}
