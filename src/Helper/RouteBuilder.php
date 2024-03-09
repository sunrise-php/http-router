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
use Stringable;
use Sunrise\Http\Router\Exception\InvalidRouteBuildingValueException;
use Sunrise\Http\Router\Exception\InvalidRouteParsingSubjectException;

use function is_int;
use function is_string;
use function sprintf;
use function str_replace;
use function substr;

/**
 * @since 3.0.0
 */
final class RouteBuilder
{
    /**
     * @throws InvalidRouteBuildingValueException
     * @throws InvalidRouteParsingSubjectException
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

                if (is_int($value)) {
                    $value = (string) $value;
                } elseif ($value instanceof BackedEnum) {
                    $value = (string) $value->value;
                } elseif ($value instanceof Stringable) {
                    $value = (string) $value;
                }

                if (!is_string($value)) {
                    throw new InvalidRouteBuildingValueException(sprintf(
                        'The route %s could not be built with an unexpected value for the variable %s.',
                        $route,
                        $variable['name'],
                    ));
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

            throw new InvalidRouteBuildingValueException(sprintf(
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
