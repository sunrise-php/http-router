<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\Language;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\Language\Language;

final class LanguageTest extends TestCase
{
    public function testConstructorWithOptionalParameters(): void
    {
        $language = new Language('sr', 'sr-RS', ['q' => '1.0']);
        $this->assertSame('sr', $language->getCode());
        $this->assertSame('sr-RS', $language->getIdentifier());
        $this->assertSame(['q' => '1.0'], $language->getParameters());
    }

    public function testConstructorWithoutOptionalParameters(): void
    {
        $language = new Language('sr');
        $this->assertSame('sr', $language->getCode());
        $this->assertSame('sr', $language->getIdentifier());
        $this->assertSame([], $language->getParameters());
    }
}
