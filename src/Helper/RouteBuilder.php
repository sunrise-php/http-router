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
     * @param array<array-key, mixed> $values
     *
     * @throws InvalidArgumentException
     */
    public static function buildRoute(string $route, array $values = []): string
    {
        $variables = RouteParser::parseRoute($route);

        $search = [];
        $replace = [];
        foreach ($variables as $variable) {
            $statement = substr($route, $variable['offset'], $variable['length']);

            if (isset($values[$variable['name']])) {
                /** @var mixed $value */
                $value = $values[$variable['name']];

                try {
                    $value = self::stringifyValue($value);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException(sprintf(
                        'The route %s could not be built due to an invalid value for the variable {%s}: %s.',
                        $route,
                        $variable['name'],
                        $e->getMessage(),
                    ));
                }

                $search[] = $statement;
                $replace[] = $value;
                continue;
            }

            if (isset($variable['optional'])) {
                $search[] = '(' . $variable['optional']['left'] . $statement . $variable['optional']['right'] . ')';
                $replace[] = '';
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The route %s could not be built because the required value for the variable {%s} is missing.',
                $route,
                $variable['name'],
            ));
        }

        // will be replaced by an empty string:
        // https://github.com/php/php-src/blob/a04577fb4ab5e1ebc7779608523b95ddf01e6c7f/ext/standard/string.c#L4406-L4408
        $search[] = '(';
        $search[] = ')';

        return str_replace($search, $replace, $route);
    }

    /**
     * Tries to cast the given value to the string type
     *
     * @throws InvalidArgumentException
     */
    public static function stringifyValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }
        if ($value instanceof Stringable) {
            return (string) $value;
        }

        throw new InvalidArgumentException(sprintf(
            'The %s value could not be converted to a string; ' .
            'supported types are: string, integer, backed enum and stringable object.',
            get_debug_type($value),
        ));
    }
}
