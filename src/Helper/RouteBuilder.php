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
use Stringable;

use function str_replace;

/**
 * Internal route builder
 *
 * @since 3.0.0
 */
final class RouteBuilder
{

    /**
     * Builds the given route with the given variables
     *
     * @param string $route
     * @param array<string, int|string|Stringable> $variables
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public static function buildRoute(string $route, array $variables = []): string
    {
        $matches = RouteParser::parseRoute($route);

        $search = [];
        $replace = [];
        foreach ($matches as $match) {
            $replacement = $variables[$match['name']] ?? $match['value'] ?? null;

            if (!isset($replacement) && !isset($match['isOptional'])) {
                throw new InvalidArgumentException();
            }

            $search[] = $match['replaceable'];
            $replace[] = (string) $replacement;
        }

        return str_replace($search, $replace, $route);
    }
}
