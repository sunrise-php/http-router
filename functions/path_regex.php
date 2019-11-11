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
use function preg_replace_callback;
use function str_replace;

/**
 * Converts the given path to Regular Expression
 *
 * @param string $path
 * @return string
 *
 * @link https://www.php.net/manual/en/regexp.reference.meta.php
 * @link https://www.php.net/manual/en/regexp.reference.subpatterns.php
 */
function path_regex(string $path) : string
{
    $patterns = [];
    $preregex = preg_replace_callback('/{(\w+)<([^<>]+)>}/', function ($matches) use (&$patterns) {
        $patterns[$matches[1]] = $matches[2];
        return '{' . $matches[1] . '}';
    }, $path);

    $preregex = addcslashes($preregex, '\^$.[]|?*+-#');
    $preregex = str_replace(['(', ')'], ['(?:', ')?'], $preregex);

    $preregex = preg_replace_callback('/{(\w+)}/', function ($matches) use ($patterns) {
        $pattern = $patterns[$matches[1]] ?? '[^/]+';
        return '(?<' . $matches[1] . '>' . $pattern . ')';
    }, $preregex);

    return '#^' . $preregex . '$#uD';
}
