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

use BackedEnum;
use InvalidArgumentException;
use Stringable;

use function addcslashes;
use function is_string;
use function sprintf;
use function str_replace;
use function substr;

/**
 * @since 3.0.0
 */
final class RouteCompiler
{
    /**
     * @return non-empty-string
     *
     * @throws InvalidArgumentException If the route isn't valid or any of the patterns are unsupported.
     */
    public static function compileRoute(string $route, array $patterns = []): string
    {
        $variables = RouteParser::parseRoute($route);

        $search = [];
        $replace = [];
        foreach ($variables as $variable) {
            $search[] = substr($route, $variable['offset'], $variable['length']);
            $replace[] = '{' . $variable['name'] . '}';
        }

        $route = str_replace($search, $replace, $route);
        $route = addcslashes($route, '#$*+-.?[\]^|');
        $route = str_replace(['(', ')'], ['(?:', ')?'], $route);

        $search = [];
        $replace = [];
        foreach ($variables as $variable) {
            $pattern = $patterns[$variable['name']] ?? $variable['pattern'] ?? '[^/]+';

            if ($pattern instanceof BackedEnum) {
                $pattern = $pattern->value;
            } elseif ($pattern instanceof Stringable) {
                $pattern = $pattern->__toString();
            }

            if (is_string($pattern)) {
                $search[] = '{' . $variable['name'] . '}';
                $replace[] = '(?<' . $variable['name'] . '>' . $pattern . ')';
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The route %s could not be compiled with an unsupported pattern for the variable %s.',
                $route,
                $variable['name'],
            ));
        }

        $route = str_replace($search, $replace, $route);

        return '#^' . $route . '$#uD';
    }
}
