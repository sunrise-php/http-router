<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Dictionary;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Dictionary\Language;

final class LanguageTest extends TestCase
{
    public function testCode(): void
    {
        self::assertSame('en', Language::English->getCode());
    }
}
