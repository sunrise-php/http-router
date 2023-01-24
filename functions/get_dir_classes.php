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
 * Scans the given directory and returns the found classes
 *
 * @param string $dirname
 *
 * @return class-string[]
 *
 * @since 3.0.0
 */
function get_dir_classes(string $dirname): array
{
    /** @var Iterator<SplFileInfo> */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirname)
    );

    $result = [];

    foreach ($files as $file) {
        // only php files...
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $classnames = get_file_classes($file->getPathname());
        foreach ($classnames as $classname) {
            $result[] = $classname;
        }
    }

    return $result;
}
