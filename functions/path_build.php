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

namespace Sunrise\Http\Router;

use Sunrise\Http\Router\Exception\RoutePathBuildException;

use function preg_match;
use function sprintf;
use function str_replace;

/**
 * Builds the given path using the given attributes
 *
 * If strict mode is enabled then each attribute value will be validated.
 *
 * @param string $path
 * @param array $attributes
 * @param bool $strict
 *
 * @return string
 *
 * @throws RoutePathBuildException
 */
function path_build(string $path, array $attributes = [], bool $strict = false) : string
{
    $result = $path;
    $matches = path_parse($path);

    foreach ($matches as $match) {
        // handle not required attributes...
        if (!isset($attributes[$match['name']])) {
            if (!$match['isOptional']) {
                $errmsg = '[%s] build error: no value given for the attribute "%s".';

                throw new RoutePathBuildException(sprintf($errmsg, $path, $match['name']), [
                    'path' => $path,
                    'match' => $match,
                ]);
            }

            $result = str_replace($match['withParentheses'], '', $result);

            continue;
        }

        $replacement = (string) $attributes[$match['name']];

        // validate the given attributes values...
        if ($strict && isset($match['pattern'])) {
            if (!preg_match('#^' . $match['pattern'] . '$#u', $replacement)) {
                $errmsg = '[%s] build error: the given value for the attribute "%s" does not match its pattern.';

                throw new RoutePathBuildException(sprintf($errmsg, $path, $match['name']), [
                    'path' => $path,
                    'value' => $replacement,
                    'match' => $match,
                ]);
            }
        }

        $result = str_replace($match['raw'], $replacement, $result);
    }

    $result = str_replace(['(', ')'], '', $result);

    return $result;
}
