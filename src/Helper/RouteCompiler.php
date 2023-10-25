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
     * Cached compiled regular expressions
     *
     * @var array<string, string>
     */
    private static array $regex = [];

    /**
     * Compiles a regular expression from the given route
     *
     * @param string $route
     *
     * @return string
     */
    public static function compileRegex(string $route): string
    {
        if (isset(self::$regex[$route])) {
            return self::$regex[$route];
        }

        $matches = RouteParser::parseRoute($route);

        foreach ($matches as $match) {
            $variable = RouteParser::buildVariable($match);

            $route = str_replace($variable, '{' . $match['name'] . '}', $route);
        }

        $route = addcslashes($route, '#$*+-.?[\]^|');

        $route = str_replace(['(', ')'], ['(?:', ')?'], $route);

        foreach ($matches as $match) {
            $pattern = $match['pattern'] ?? null;
            if (isset($pattern, Router::$patterns[$pattern])) {
                $pattern = Router::$patterns[$pattern];
            }

            $subpattern = '(?<' . $match['name'] . '>' . ($pattern ?? '[^/]+') . ')';

            $route = str_replace('{' . $match['name'] . '}', $subpattern, $route);
        }

        return self::$regex[$route] = '#^' . $route . '$#uD';
    }
}
