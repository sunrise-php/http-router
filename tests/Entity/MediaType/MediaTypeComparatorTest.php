<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\MediaType;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeComparator;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;

final class MediaTypeComparatorTest extends TestCase
{
    #[DataProvider('compareDataProvider')]
    public function testCompare(string $aType, string $aSubtype, string $bType, string $bSubtype, int $expectedResult): void
    {
        $comparator = new MediaTypeComparator();

        $a = $this->mockMediaType($aType, $aSubtype);
        $b = $this->mockMediaType($bType, $bSubtype);

        $this->assertSame($expectedResult, $comparator->compare($a, $b));
    }

    public static function compareDataProvider(): iterable
    {
        yield [
            'application', 'json',
            'application', 'json',
            0,
        ];

        yield [
            'APPLICATION', 'JSON',
            'application', 'json',
            0,
        ];

        yield [
            'application', 'json',
            'APPLICATION', 'JSON',
            0,
        ];

        yield [
            '*', '*',
            '*', '*',
            0,
        ];

        yield [
            '*', '*',
            'application', 'json',
            0,
        ];

        yield [
            'application', 'json',
            '*', '*',
            0,
        ];

        yield [
            'image', '*',
            'image', '*',
            0,
        ];

        yield [
            'image', '*',
            'image', 'webp',
            0,
        ];

        yield [
            'image', 'webp',
            'image', '*',
            0,
        ];

        yield [
            'application', 'json',
            'application', 'xml',
            'application/json' <=> 'application/xml',
        ];

        yield [
            'application', 'xml',
            'application', 'json',
            'application/xml' <=> 'application/json',
        ];
    }

    private function mockMediaType(string $type, string $subtype): MediaTypeInterface&MockObject
    {
        $mediaTypeMock = $this->createMock(MediaTypeInterface::class);
        $mediaTypeMock->method('getType')->willReturn($type);
        $mediaTypeMock->method('getSubtype')->willReturn($subtype);
        $mediaTypeMock->method('__toString')->willReturn($type . '/' . $subtype);

        return $mediaTypeMock;
    }
}
