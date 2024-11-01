<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\Language;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\Language\LanguageComparator;
use Sunrise\Http\Router\Entity\Language\LanguageInterface;

final class LanguageComparatorTest extends TestCase
{
    /**
     * @dataProvider compareDataProvider
     */
    public function testCompare(LanguageInterface $a, LanguageInterface $b, int $expected): void
    {
        $this->assertSame($expected, (new LanguageComparator())->compare($a, $b));
    }

    private function compareDataProvider(): iterable
    {
        yield [
            $this->mockLanguage('sr'),
            $this->mockLanguage('sr'),
            0,
        ];

        yield [
            $this->mockLanguage('*'),
            $this->mockLanguage('*'),
            0,
        ];

        yield [
            $this->mockLanguage('*'),
            $this->mockLanguage('sr'),
            0,
        ];

        yield [
            $this->mockLanguage('sr'),
            $this->mockLanguage('*'),
            0,
        ];

        yield [
            $this->mockLanguage('sr'),
            $this->mockLanguage('bs'),
            'sr' <=> 'bs',
        ];

        yield [
            $this->mockLanguage('bs'),
            $this->mockLanguage('sr'),
            'bs' <=> 'sr',
        ];
    }

    private function mockLanguage(string $code): LanguageInterface&MockObject
    {
        $languageMock = $this->createMock(LanguageInterface::class);
        $languageMock->method('getCode')->willReturn($code);

        return $languageMock;
    }
}
