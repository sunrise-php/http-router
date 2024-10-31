<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\Language;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\Language\ServerLanguage;

final class ServerLanguageTest extends TestCase
{
    public function testConstructor(): void
    {
        $language = new ServerLanguage('sr');

        $this->assertSame('sr', $language->getCode());
    }
}
