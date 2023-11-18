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

use Sunrise\Http\Router\Router;

use function addcslashes;
use function str_replace;

/**
 * Internal route compiler
 *
 * @since 3.0.0
 */
final class RouteCompiler
{

    /**
     * Compiles a regular expression from the given route
     *
     * @param string $route
     *
     * @return string
     */
    public static function compileRegex(string $route): string
    {
        $matches = RouteParser::parseRoute($route);

        foreach ($matches as $match) {
            $route = str_replace($match['variable'], '{' . $match['name'] . '}', $route);
        }

        $route = addcslashes($route, '#$*+-.?[\]^|');
        $route = str_replace(['(', ')'], ['(?:', ')?'], $route);

        foreach ($matches as $match) {
            $pattern = $match['pattern'] ?? '[^/]+';
            if (isset(Router::$patterns[$pattern])) {
                $pattern = Router::$patterns[$pattern];
            }

            $subpattern = '(?<' . $match['name'] . '>' . $pattern . ')';

            $route = str_replace('{' . $match['name'] . '}', $subpattern, $route);
        }

        return '#^' . $route . '$#uD';
    }
}
