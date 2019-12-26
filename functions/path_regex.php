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
use function addcslashes;
use function str_replace;

/**
 * Converts the given path to Regular Expression
 *
 * @param string $path
 *
 * @return string
 */
function path_regex(string $path) : string
{
    $matches = path_parse($path);

    foreach ($matches as $match) {
        $path = str_replace($match['raw'], '{' . $match['name'] . '}', $path);
    }

    $path = addcslashes($path, '#$*+-.?[\]^|');
    $path = str_replace(['(', ')'], ['(?:', ')?'], $path);

    foreach ($matches as $match) {
        $pattern = $match['pattern'] ?? '[^/]+';
        $subpattern = '(?<' . $match['name'] . '>' . $pattern . ')';

        $path = str_replace('{' . $match['name'] . '}', $subpattern, $path);
    }

    return '#^' . $path . '$#uD';
}
