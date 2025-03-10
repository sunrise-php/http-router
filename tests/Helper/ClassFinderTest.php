<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Helper;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Helper\ClassFinder;

final class ClassFinderTest extends TestCase
{
    public function testDirDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The directory "/50a6179b-d891-4983-9bb2-9b54d3ba5aaa" does not exist.');
        ClassFinder::getDirClasses('/50a6179b-d891-4983-9bb2-9b54d3ba5aaa')->valid();
    }

    public function testFileDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The file "/6a219804-35a6-4586-8dd5-6db25894e499" does not exist.');
        ClassFinder::getFileClasses('/6a219804-35a6-4586-8dd5-6db25894e499')->valid();
    }
}
