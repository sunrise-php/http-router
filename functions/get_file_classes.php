<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import functions
 */
use function array_diff;
use function get_declared_classes;

/**
 * Scans the given file and returns the found classes
 *
 * @param string $filename
 *
 * @return class-string[]
 *
 * @since 3.0.0
 *
 * @todo https://www.php.net/manual/en/book.tokenizer.php
 */
function get_file_classes(string $filename): array
{
    $snapshot = get_declared_classes();

    require_once $filename;

    return array_diff(get_declared_classes(), $snapshot);
}
