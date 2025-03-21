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

use InvalidArgumentException;

use function addcslashes;
use function str_replace;

/**
 * @since 3.0.0
 */
final class RouteCompiler
{
    public const DEFAULT_VARIABLE_PATTERN = '[^/]+';
    public const EXPRESSION_DELIMITER = '#';

    /**
     * @param array<string, string> $patterns
     *
     * @return non-empty-string
     *
     * @throws InvalidArgumentException
     */
    public static function compileRoute(string $route, array $patterns = []): string
    {
        $variables = RouteParser::parseRoute($route);

        $search = [];
        $replace = [];
        foreach ($variables as $variable) {
            $search[] = $variable['statement'];
            $replace[] = '{' . $variable['name'] . '}';
        }

        $route = str_replace($search, $replace, $route);
        $route = addcslashes($route, '#$*+-.?[\]^|');
        $route = str_replace(['(', ')'], ['(?:', ')?'], $route);

        $search = [];
        $replace = [];
        foreach ($variables as $variable) {
            $pattern = $patterns[$variable['name']] ?? $variable['pattern'] ?? self::DEFAULT_VARIABLE_PATTERN;

            $search[] = '{' . $variable['name'] . '}';
            $replace[] = '(?<' . $variable['name'] . '>' . $pattern . ')';
        }

        $route = str_replace($search, $replace, $route);

        return '#^' . $route . '$#uD';
    }
}
