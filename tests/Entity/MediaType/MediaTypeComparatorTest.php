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
    public function testCompare(string $aId, string $bId, int $expected): void
    {
        $a = $this->mockMediaType(identifier: $aId);
        $b = $this->mockMediaType(identifier: $bId);

        $this->assertSame($expected, (new MediaTypeComparator())->compare($a, $b));
    }

    public static function compareDataProvider(): iterable
    {
        yield [
            'application/json',
            'application/json',
            0,
        ];

        yield [
            'application/json',
            'APPLICATION/JSON',
            0,
        ];

        yield [
            'APPLICATION/JSON',
            'application/json',
            0,
        ];

        yield [
            '*/*',
            '*/*',
            0,
        ];

        yield [
            'application/json',
            '*/*',
            0,
        ];

        yield [
            '*/*',
            'application/json',
            0,
        ];

        yield [
            '*/json',
            'application/*',
            0,
        ];

        yield [
            'application/*',
            '*/json',
            0,
        ];

        yield [
            'image/*',
            'image/*',
            0,
        ];

        yield [
            '*/webp',
            '*/webp',
            0,
        ];

        yield [
            'image/*',
            'image/webp',
            0,
        ];

        yield [
            'image/webp',
            'image/*',
            0,
        ];

        yield [
            'image/*',
            'video/*',
            -1,
        ];

        yield [
            '*/jpeg',
            '*/png',
            -1,
        ];

        yield [
            'application/json',
            'application/xml',
            -1,
        ];

        yield [
            'application/xml',
            'application/json',
            1,
        ];
    }

    private function mockMediaType(string $identifier): MediaTypeInterface&MockObject
    {
        $mediaType = $this->createMock(MediaTypeInterface::class);
        $mediaType->method('getIdentifier')->willReturn($identifier);

        return $mediaType;
    }
}
