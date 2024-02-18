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
     * @throws InvalidArgumentException
     *         If the route isn't valid,
     *         or any of the values are unsupported,
     *         or any of the required values are missing.
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
                    $value = $value->__toString();
                }

                if (is_string($value)) {
                    $search[] = $statement;
                    $replace[] = $value;
                    continue;
                }

                throw new InvalidArgumentException(sprintf(
                    'The route %s could not be built with an unsupported value for the variable %s.',
                    $route,
                    $variable['name'],
                ));
            }

            if (isset($variable['optional'])) {
                $search[] = '(' . ($variable['left'] ?? '') . $statement . ($variable['right'] ?? '') . ')';
                $replace[] = '';
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'The route %s could not be built without a required value for the variable %s.',
                $route,
                $variable['name'],
            ));
        }

        return str_replace([...$search, '(', ')'], $replace, $route);
    }
}
