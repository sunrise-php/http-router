<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\Language;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\Locale\LocaleComparator;
use Sunrise\Http\Router\Entity\Locale\LocaleInterface;

final class LanguageComparatorTest extends TestCase
{
    #[DataProvider('compareDataProvider')]
    public function testCompare(string $aCode, string $bCode, int $expected): void
    {
        $a = $this->mockLanguage(code: $aCode);
        $b = $this->mockLanguage(code: $bCode);

        $this->assertSame($expected, (new LocaleComparator())->compare($a, $b));
    }

    public static function compareDataProvider(): iterable
    {
        yield [
            'sr',
            'sr',
            0,
        ];

        yield [
            '*',
            '*',
            0,
        ];

        yield [
            '*',
            'sr',
            0,
        ];

        yield [
            'sr',
            '*',
            0,
        ];

        yield [
            'sr',
            'bs',
            1,
        ];

        yield [
            'bs',
            'sr',
            -1,
        ];

        yield [
            'it',
            'IT',
            1,
        ];

        yield [
            'IT',
            'it',
            -1,
        ];

        yield [
            '',
            '',
            0,
        ];

        yield [
            'sr',
            '',
            1,
        ];

        yield [
            '',
            'sr',
            -1,
        ];
    }

    private function mockLanguage(string $code): LocaleInterface&MockObject
    {
        $language = $this->createMock(LocaleInterface::class);
        $language->method('getLanguageCode')->willReturn($code);

        return $language;
    }
}
