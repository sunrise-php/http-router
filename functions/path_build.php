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
use function addcslashes;
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
        // handle not required attributes...
        if (!isset($attributes[$match['name']])) {
            if (!$match['isOptional']) {
                $errmsg = '[%s] build error: no value given for the attribute "%s".';

                throw new InvalidArgumentException(
                    sprintf($errmsg, $path, $match['name'])
                );
            }

            $path = str_replace($match['withParentheses'], '', $path);

            continue;
        }

        $replacement = (string) $attributes[$match['name']];

        // validate the given attributes values...
        if ($strict && isset($match['pattern'])) {
            $pattern = addcslashes($match['pattern'], '#');

            if (!preg_match('#^' . $pattern . '$#u', $replacement)) {
                $errmsg = '[%s] build error: the given value for the attribute "%s" does not match its pattern.';

                throw new InvalidArgumentException(
                    sprintf($errmsg, $path, $match['name'])
                );
            }
        }

        $path = str_replace($match['raw'], $replacement, $path);
    }

    $path = str_replace(['(', ')'], '', $path);

    return $path;
}
