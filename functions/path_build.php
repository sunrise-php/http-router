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
use Sunrise\Http\Router\Exception\InvalidAttributeValueException;
use Sunrise\Http\Router\Exception\MissingAttributeValueException;

/**
 * Import functions
 */
use function array_search;
use function array_map;
use function str_replace;
use function strtr;
use function preg_match;

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
 * @throws InvalidAttributeValueException
 * @throws MissingAttributeValueException
 */
function path_build(string $path, array $attributes = [], bool $strict = false) : string
{
    /**
     * @var array[] result cache as pairs: $result(builded path) => [$path, $attributes]
     */
    static $buildedPaths = [];
    
    // normalize attribute values
    if ($attributes) {
        $attributes = array_map('rawurlencode', $attributes);
        // decode slashes back, see Apache docs about AllowEncodedSlashes and AcceptPathInfo
        $attributes = str_replace(['%2F', '%5C'], ['/', '\\'], $attributes);
    }
    
    // find cached result
    $result = array_search([$path, $attributes], $buildedPaths);
    if ($result) {
        return $result;
    }
    
    $result = $path;
    $matches = path_parse($path);

    foreach ($matches as $match) {
        // handle not required attributes...
        if (!isset($attributes[$match['name']])) {
            if (!$match['isOptional']) {
                throw new MissingAttributeValueException(strtr(
                    '[:path] build error: no value given for the attribute ":attr".',
                    [':path' => $path, ':attr' => $match['name']]
                ));
            }

            $result = str_replace($match['withParentheses'], '', $result);

            continue;
        }

        $replacement = (string) $attributes[$match['name']];

        // validate the given attributes values...
        if ($strict && isset($match['pattern'])) {
            if (!preg_match('#^' . $match['pattern'] . '$#u', $replacement)) {
                throw new InvalidAttributeValueException(strtr(
                    '[:path] build error: the given value for the attribute ":attr" does not match its pattern.',
                    [':path' => $path, ':attr' => $match['name']]
                ));
            }
        }

        $result = str_replace($match['raw'], $replacement, $result);
    }

    $result = str_replace(['(', ')'], '', $result);
    
    // cache result
    $buildedPaths[$result] = [$path, $attributes];

    return $result;
}
