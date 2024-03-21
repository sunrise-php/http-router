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

use Generator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;
use SplFileInfo;
use SplStack;

use function get_declared_classes;
use function is_dir;
use function is_file;
use function realpath;
use function sprintf;

/**
 * @since 3.0.0
 */
final class ClassFinder
{
    /**
     * @return Generator<int, ReflectionClass>
     *
     * @throws InvalidArgumentException If the directory doesn't exist.
     */
    public static function getDirClasses(string $dirname): Generator
    {
        if (!is_dir($dirname)) {
            throw new InvalidArgumentException(sprintf('The directory %s does not exist.', $dirname));
        }

        /** @var iterable<string, SplFileInfo> $files */
        $files = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirname)), '/\.php$/');

        /** @var array<string, true> $filenames */
        $filenames = [];
        foreach ($files as $file) {
            $filenames[$file->getRealPath()] = true;

            (static function (string $filename): void {
                require_once $filename;
            })($file->getRealPath());
        }

        foreach (get_declared_classes() as $className) {
            $classReflection = new ReflectionClass($className);
            if (isset($filenames[$classReflection->getFileName()])) {
                yield $classReflection;
            }
        }
    }

    /**
     * @return Generator<int, ReflectionClass>
     *
     * @throws InvalidArgumentException If the file doesn't exist.
     */
    public static function getFileClasses(string $filename): Generator
    {
        if (!is_file($filename)) {
            throw new InvalidArgumentException(sprintf('The file %s does not exist.', $filename));
        }

        $filename = realpath($filename);

        (static function (string $filename): void {
            require_once $filename;
        })($filename);

        foreach (get_declared_classes() as $className) {
            $classReflection = new ReflectionClass($className);
            if ($classReflection->getFileName() === $filename) {
                yield $classReflection;
            }
        }
    }

    /**
     * @return SplStack<ReflectionClass>
     */
    public static function getParentClasses(ReflectionClass $class): SplStack
    {
        /** @var SplStack<ReflectionClass> $parents */
        $parents = new SplStack();
        while ($class = $class->getParentClass()) {
            $parents->push($class);
        }

        return $parents;
    }
}
