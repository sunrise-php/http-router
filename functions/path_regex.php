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
use function preg_match_all;

/**
 * Import constants
 */
use const PREG_SET_ORDER;

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
    preg_match_all('/{([0-9A-Za-z_]+)(?:<([^<#>]+)>)?}/', $path, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $path = str_replace($match[0], '{'.$match[1].'}', $path);
    }

    $path = addcslashes($path, '#$*+-.?[\]^|');
    $path = str_replace(['(', ')'], ['(?:', ')?'], $path);

    foreach ($matches as $match) {
        $path = str_replace('{'.$match[1].'}', '(?<'.$match[1].'>'.($match[2] ?? '[^/]+').')', $path);
    }

    return '#^'.$path.'$#uD';
}
