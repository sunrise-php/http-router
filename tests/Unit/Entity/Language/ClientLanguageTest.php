<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Unit\Entity\Language;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\Language\ClientLanguage;

final class ClientLanguageTest extends TestCase
{
    public function testConstructor(): void
    {
        $language = new ClientLanguage('sr', 'sr-RS', ['q' => '1.0']);

        $this->assertSame('sr', $language->getCode());
        $this->assertSame('sr-RS', $language->getIdentifier());
        $this->assertSame(['q' => '1.0'], $language->getParameters());
    }
}
