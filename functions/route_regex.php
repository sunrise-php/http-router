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
 * Creates Regular Expression for the given path from the given patterns
 *
 * @param string $path
 * @param array $patterns
 *
 * @return string
 */
function route_regex(string $path, array $patterns) : string
{
	$regex = \addcslashes($path, '.\+*?[^]$=!<>|:-#');

	$regex = \str_replace(['(', ')'], ['(?:', ')?'], $regex);

	$regex = \preg_replace_callback('/{(\w+)}/', function($match) use($patterns)
	{
		$pattern = $patterns[$match[1]] ?? '[^/]+';

		return '(?<' . $match[1] . '>' . $pattern . ')';

	}, $regex);

	return '#^' . $regex . '$#ui';
}
