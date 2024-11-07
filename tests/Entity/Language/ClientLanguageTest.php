<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\Language;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\Locale\Locale;

final class ClientLanguageTest extends TestCase
{
    public function testConstructor(): void
    {
        $clientLanguage = new Locale('sr', 'sr-RS', ['q' => '1.0']);
        $this->assertSame('sr', $clientLanguage->getLanguageCode());
        $this->assertSame('sr-RS', $clientLanguage->getLanguageCode());
        $this->assertSame(['q' => '1.0'], $clientLanguage->getParameters());
    }
}
