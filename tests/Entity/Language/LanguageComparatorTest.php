<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\Language;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\Language\LanguageComparator;
use Sunrise\Http\Router\Entity\Language\LanguageInterface;

final class LanguageComparatorTest extends TestCase
{
    #[DataProvider('compareDataProvider')]
    public function testCompare(string $aCode, string $bCode, int $expectedResult): void
    {
        $comparator = new LanguageComparator();

        $a = $this->mockLanguage($aCode);
        $b = $this->mockLanguage($bCode);

        $this->assertSame($expectedResult, $comparator->compare($a, $b));
    }

    public static function compareDataProvider(): iterable
    {
        yield ['sr', 'sr', 0];
        yield ['*', '*', 0];
        yield ['*', 'sr', 0];
        yield ['sr', '*', 0];
        yield ['sr', 'bs', 'sr' <=> 'bs'];
        yield ['bs', 'sr', 'bs' <=> 'sr'];
    }

    private function mockLanguage(string $code): LanguageInterface&MockObject
    {
        $languageMock = $this->createMock(LanguageInterface::class);
        $languageMock->method('getCode')->willReturn($code);

        return $languageMock;
    }
}
