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
use function substr;

/**
 * @since 3.0.0
 */
final class RouteSimplifier
{
    /**
     * @throws InvalidArgumentException If the route isn't valid.
     */
    public static function simplifyRoute(string $route): string
    {
        $variables = RouteParser::parseRoute($route);

        $search = [];
        $replace = [];
        foreach ($variables as $variable) {
            $search[] = substr($route, $variable['offset'], $variable['length']);
            $replace[] = '{' . $variable['name'] . '}';
        }

        $search[] = '(';
        $search[] = ')';

        return str_replace($search, $replace, $route);
    }
}