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

use FilesystemIterator;
use Generator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;

use function get_declared_classes;
use function is_dir;
use function sprintf;

/**
 * @since 3.0.0
 */
final class FilesystemHelper
{
    /**
     * @return Generator<int, ReflectionClass>
     *
     * @throws InvalidArgumentException If the directory doesn't exist.
     */
    public static function getDirectoryClasses(string $dirname): Generator
    {
        if (!is_dir($dirname)) {
            throw new InvalidArgumentException(sprintf(
                'The directory %s could not be scanned because it does not exist.',
                $dirname,
            ));
        }

        /** @var iterable<string, string> $iterator */
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dirname,
                    FilesystemIterator::KEY_AS_PATHNAME
                    | FilesystemIterator::CURRENT_AS_PATHNAME
                    | FilesystemIterator::SKIP_DOTS,
                ),
                RecursiveIteratorIterator::LEAVES_ONLY,
            ),
            '/\.php$/',
            RegexIterator::MATCH,
        );

        /** @var array<string, string> $filenames */
        $filenames = [...$iterator];

        foreach ($filenames as $filename) {
            (static function (string $filename): void {
                /** @psalm-suppress UnresolvableInclude */
                require_once $filename;
            })($filename);
        }

        foreach (get_declared_classes() as $className) {
            $classReflection = new ReflectionClass($className);
            if (isset($filenames[$classReflection->getFileName()])) {
                yield $classReflection;
            }
        }
    }
}
