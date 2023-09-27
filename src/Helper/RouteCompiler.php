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

namespace Sunrise\Http\Router\Helper;

use function addcslashes;
use function str_replace;

/**
 * Internal route compiler
 *
 * @internal
 */
final class RouteCompiler
{
    public static function compileRegex(string $route): string
    {
        $matches = RouteParser::parseRoute($route);

        foreach ($matches as $match) {
            $variable = '{' . $match['name'];

            if (isset($match['value'])) {
                $variable .= '=' . $match['value'];
            }
            if (isset($match['pattern'])) {
                $variable .= '<' . $match['pattern'] . '>';
            }

            $variable .= '}';

            $route = str_replace($variable, '{' . $match['name'] . '}', $route);
        }

        $route = addcslashes($route, '#$*+-.?[\]^|');

        $route = str_replace(['(', ')'], ['(?:', ')?'], $route);

        foreach ($matches as $match) {
            $subpattern = '(?<' . $match['name'] . '>' . ($match['pattern'] ?? '[^/]+') . ')';

            $route = str_replace('{' . $match['name'] . '}', $subpattern, $route);
        }

        return '#^' . $route . '$#uD';
    }
}
