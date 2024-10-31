<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\Language;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\Language\LanguageComparator;
use Sunrise\Http\Router\Entity\Language\LanguageInterface;
use Sunrise\Http\Router\Entity\Language\ServerLanguage;

final class LanguageComparatorTest extends TestCase
{
    /**
     * @dataProvider compareDataProvider
     */
    public function testCompare(LanguageInterface $a, LanguageInterface $b, int $expected): void
    {
        $this->assertSame($expected, (new LanguageComparator)->compare($a, $b));
    }

    private function compareDataProvider(): iterable
    {
        yield [
            new ServerLanguage('sr'),
            new ServerLanguage('sr'),
            0,
        ];

        yield [
            new ServerLanguage('*'),
            new ServerLanguage('*'),
            0,
        ];

        yield [
            new ServerLanguage('*'),
            new ServerLanguage('sr'),
            0,
        ];

        yield [
            new ServerLanguage('sr'),
            new ServerLanguage('*'),
            0,
        ];

        yield [
            new ServerLanguage('sr'),
            new ServerLanguage('bs'),
            'sr' <=> 'bs',
        ];

        yield [
            new ServerLanguage('bs'),
            new ServerLanguage('sr'),
            'bs' <=> 'sr',
        ];
    }
}
