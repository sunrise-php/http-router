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
