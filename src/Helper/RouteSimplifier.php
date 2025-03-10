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

use function str_replace;

/**
 * @since 3.0.0
 */
final class RouteSimplifier
{
    /**
     * @throws InvalidArgumentException
     */
    public static function simplifyRoute(string $route): string
    {
        $variables = RouteParser::parseRoute($route);

        $search = [];
        $replace = [];
        foreach ($variables as $variable) {
            $search[] = $variable['statement'];
            $replace[] = '{' . $variable['name'] . '}';
        }

        // will be replaced by an empty string:
        // https://github.com/php/php-src/blob/a04577fb4ab5e1ebc7779608523b95ddf01e6c7f/ext/standard/string.c#L4406-L4408
        $search[] = '(';
        $search[] = ')';

        return str_replace($search, $replace, $route);
    }
}
