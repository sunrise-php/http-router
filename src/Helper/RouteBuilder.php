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

use function get_debug_type;
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
                try {
                    $value = self::stringifyValue($values[$variable['name']]);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be built with an invalid value for the variable %s due to: %s',
                        $route,
                        $variable['name'],
                        $e->getMessage(),
                    ), previous: $e);
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

    /**
     * @throws InvalidArgumentException If the value couldn't be converted to a string.
     */
    public static function stringifyValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        } elseif (is_int($value)) {
            return (string) $value;
        } elseif ($value instanceof BackedEnum) {
            return (string) $value->value;
        } elseif ($value instanceof Stringable) {
            return $value->__toString();
        }

        throw new InvalidArgumentException(sprintf(
            'The value of the type %s could not be converted to a string. ' .
            'To resolve this issue, use one of the following value types: ' .
            'string, integer, backed enum or stringable object.',
            get_debug_type($value),
        ));
    }
}
