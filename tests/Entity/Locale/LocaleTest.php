<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\Locale;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Locale;

final class LocaleTest extends TestCase
{
    public function testConstructor(): void
    {
        $locale = new Locale('sr', 'RS');
        $this->assertSame('sr', $locale->getLanguageCode());
        $this->assertSame('RS', $locale->getRegionCode());
    }
}
