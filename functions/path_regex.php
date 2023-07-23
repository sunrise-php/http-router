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
    // This will be useful for a long-running application,
    // for example if you use the RoadRunner server...
    static $cache = [];

    if (isset($cache[$path])) {
        return $cache[$path];
    }

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

    $cache[$path] = '#^' . $path . '$#uD';

    return $cache[$path];
}
