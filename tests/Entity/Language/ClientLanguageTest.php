<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\Language;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\Language\ClientLanguage;

final class ClientLanguageTest extends TestCase
{
    public function testConstructor(): void
    {
        $clientLanguage = new ClientLanguage('sr', 'sr-RS', ['q' => '1.0']);
        $this->assertSame('sr', $clientLanguage->getCode());
        $this->assertSame('sr-RS', $clientLanguage->getLocale());
        $this->assertSame(['q' => '1.0'], $clientLanguage->getParameters());
    }
}
