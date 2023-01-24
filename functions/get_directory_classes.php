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
 * Import classes
 */
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Import functions
 */
use function array_diff;
use function get_declared_classes;

/**
 * Scans the given directory and returns the found classes
 *
 * @param string $directory
 *
 * @return class-string[]
 *
 * @since 3.0.0
 */
function get_directory_classes(string $directory): array
{
    $known = get_declared_classes();

    /** @var Iterator<SplFileInfo> */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );

    foreach ($files as $file) {
        if ('php' === $file->getExtension()) {
            /** @psalm-suppress UnresolvableInclude */
            require_once $file->getPathname();
        }
    }

    return array_diff(get_declared_classes(), $known);
}
