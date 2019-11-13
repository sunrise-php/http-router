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
use function preg_replace_callback;
use function sprintf;

/**
 * Builds the given path with the given attributes
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
    $regex = '/{([0-9A-Za-z_]+)(?:<([^<#>]+)>)?}/';

    return preg_replace_callback($regex, function ($match) use ($path, $attributes, $strict) {
        if (!isset($attributes[$match[1]])) {
            throw new InvalidArgumentException(
                sprintf('[%s] missing attribute "%s".', $path, $match[1])
            );
        }

        $attributes[$match[1]] = (string) $attributes[$match[1]];

        if ($strict && isset($match[2])) {
            if (!preg_match('#'.$match[2].'#u', $attributes[$match[1]])) {
                throw new InvalidArgumentException(
                    sprintf('[%s] "%s" must match "%s".', $path, $match[1], $match[2])
                );
            }
        }

        return $attributes[$match[1]];
    }, $path);
}
