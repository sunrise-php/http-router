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

use function preg_match_all, addcslashes, str_replace, empty;
use const PREG_SET_ORDER;

/**
 * Converts the given path to regular expression.
 *
 * @param string $path source path
 * @return string compiled regular expression
 */
function path_regex(string $path) : string
{
    preg_match_all('/{([-_a-z0-9]+)(<([^>]*)>)?}/i', $path, $attributes, PREG_SET_ORDER);
    foreach ($attributes as $matches) {
        $path = str_replace($matches[0], '{' . $matches[1] . '}', $path);
    }
    
    $path = addcslashes($path, '.\+*?[^]$=!<>|:-#');
    $path = str_replace(['(', ')'], ['(?:', ')?'], $path);
    
    foreach ($attributes as $matches) {
        $pattern = empty($matches[3]) ? '[^/<>()]+' : $matches[3];
        $path = str_replace(
            '{' . $matches[1] . '}', 
            '(?<' . $matches[1] . '>' . $pattern . ')', 
            $path
        );
    }

    return '#^' . $path . '$#uD';
}
