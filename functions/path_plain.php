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

use function str_replace;

/**
 * Simplifies the given path
 *
 * @param string $path
 *
 * @return string
 */
function path_plain(string $path) : string
{
    $attrs = path_parse($path);

    foreach ($attrs as $attr) {
        $path = str_replace($attr['raw'], '{' . $attr['name'] . '}', $path);
    }

    return str_replace(['(', ')'], '', $path);
}
