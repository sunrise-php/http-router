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

use function sprintf;
use function str_replace;
use function substr;

/**
 * @since 3.0.0
 */
final class RouteBuilder
{
    /**
     * @throws InvalidArgumentException If the route isn't valid or any of the required values are missing.
     */
    public static function buildRoute(string $route, array $values = []): string
    {
        $variables = RouteParser::parseRoute($route);

        $search = [];
        $replace = [];
        foreach ($variables as $variable) {
            $statement = substr($route, $variable['offset'], $variable['length']);

            if (isset($values[$variable['name']])) {
                $value = $values[$variable['name']];

                if ($value instanceof BackedEnum) {
                    $value = $value->value;
                }

                $search[] = $statement;
                $replace[] = $value;
                continue;
            }

            if (isset($variable['optional'])) {
                $left = $variable['left'] ?? '';
                $right = $variable['right'] ?? '';

                $search[] = '(' . $left . $statement . $right . ')';
                $replace[] = '';
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The route %s could not be built without a required value for the variable %s.',
                $route,
                $variable['name'],
            ));
        }

        $search[] = '(';
        $search[] = ')';

        return str_replace($search, $replace, $route);
    }
}
