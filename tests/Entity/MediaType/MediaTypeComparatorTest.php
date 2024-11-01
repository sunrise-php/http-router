<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\MediaType;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeComparator;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;

final class MediaTypeComparatorTest extends TestCase
{
    /**
     * @dataProvider compareDataProvider
     */
    public function testCompare(MediaTypeInterface $a, MediaTypeInterface $b, int $expected): void
    {
        $this->assertSame($expected, (new MediaTypeComparator())->compare($a, $b));
    }

    private function compareDataProvider(): iterable
    {
        yield [
            $this->mockMediaType('application', 'json'),
            $this->mockMediaType('application', 'json'),
            0,
        ];

        yield [
            $this->mockMediaType('*', '*'),
            $this->mockMediaType('*', '*'),
            0,
        ];

        yield [
            $this->mockMediaType('*', '*'),
            $this->mockMediaType('application', 'json'),
            0,
        ];

        yield [
            $this->mockMediaType('application', 'json'),
            $this->mockMediaType('*', '*'),
            0,
        ];

        yield [
            $this->mockMediaType('image', '*'),
            $this->mockMediaType('image', '*'),
            0,
        ];

        yield [
            $this->mockMediaType('image', '*'),
            $this->mockMediaType('image', 'webp'),
            0,
        ];

        yield [
            $this->mockMediaType('image', 'webp'),
            $this->mockMediaType('image', '*'),
            0,
        ];

        yield [
            $this->mockMediaType('application', 'json'),
            $this->mockMediaType('application', 'xml'),
            'application/json' <=> 'application/xml',
        ];

        yield [
            $this->mockMediaType('application', 'xml'),
            $this->mockMediaType('application', 'json'),
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
