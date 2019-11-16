<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use InvalidArgumentException;

/**
 * Import functions
 */
use function preg_match;
use function sprintf;

/**
 * Builds the given path using the given attributes
 *
 * If strict mode is enabled, each attribute value will be validated.
 *
 * @param string $path
 * @param array $attributes
 * @param bool $strict
 *
 * @return string
 *
 * @throws InvalidArgumentException
 */
function path_build(string $path, array $attributes = [], bool $strict = false) : string
{
    $matches = path_parse($path);

    foreach ($matches as $match) {
        if (!isset($attributes[$match['name']])) {
            if (!$match['isOptional']) {
                throw new InvalidArgumentException(
                    sprintf('[%s] missing attribute "%s".', $path, $match['name'])
                );
            }

            $path = str_replace($match['withParentheses'], '', $path);
        }

        $attributes[$match['name']] = (string) $attributes[$match['name']];

        if ($strict && isset($match['pattern'])) {
            if (!preg_match('#' . $match['pattern'] . '#u', $attributes[$match['name']])) {
                throw new InvalidArgumentException(
                    sprintf('[%s] "%s" must match "%s".', $path, $match['name'], $match['pattern'])
                );
            }
        }

        $path = str_replace($match['raw'], $attributes[$match['name']], $path);
    }

    $path = str_replace(['(', ')'], '', $path);

    return $path;
}
