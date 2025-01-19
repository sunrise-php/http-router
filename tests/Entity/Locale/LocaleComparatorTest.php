<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\Locale;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Helper\LocaleComparator;
use Sunrise\Http\Router\LocaleInterface;

final class LocaleComparatorTest extends TestCase
{
    #[DataProvider('compareDataProvider')]
    public function testCompare(
        string $aLanguageCode,
        ?string $aRegionCode,
        string $bLanguageCode,
        ?string $bRegionCode,
        int $expectedResult,
    ): void {
        $a = $this->mockLocale($aLanguageCode, $aRegionCode);
        $b = $this->mockLocale($bLanguageCode, $bRegionCode);

        $actualResult = LocaleComparator::compareLocales($a, $b);

        $this->assertSame($expectedResult, $actualResult);
    }

    public static function compareDataProvider(): iterable
    {
        yield ['sr', null, 'sr', null, 0];
        yield ['sr', null, 'bs', null, 1];
        yield ['bs', null, 'sr', null, -1];

        yield ['sr', null, 'SR', null, 1];
        yield ['SR', null, 'sr', null, -1];

        yield ['*', null, '*', null, 0];
        yield ['sr', null, '*', null, 0];
        yield ['*', null, 'sr', null, 0];

        yield ['sr', 'RS', 'sr', 'RS', 0];
        yield ['sr', 'RS', 'sr', 'ME', 1];
        yield ['sr', 'ME', 'sr', 'RS', -1];
    }

    private function mockLocale(string $languageCode, ?string $regionCode = null): LocaleInterface&MockObject
    {
        $language = $this->createMock(LocaleInterface::class);
        $language->method('getLanguageCode')->willReturn($languageCode);
        $language->method('getRegionCode')->willReturn($regionCode);

        return $language;
    }
}
