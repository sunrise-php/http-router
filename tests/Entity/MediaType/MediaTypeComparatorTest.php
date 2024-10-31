<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\MediaType;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeComparator;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;
use Sunrise\Http\Router\Entity\MediaType\ServerMediaType;

final class MediaTypeComparatorTest extends TestCase
{
    /**
     * @dataProvider compareDataProvider
     */
    public function testCompare(MediaTypeInterface $a, MediaTypeInterface $b, int $expected): void
    {
        $this->assertSame($expected, (new MediaTypeComparator)->compare($a, $b));
    }

    private function compareDataProvider(): iterable
    {
        yield [
            new ServerMediaType('application', 'json'),
            new ServerMediaType('application', 'json'),
            0,
        ];

        yield [
            new ServerMediaType('*', '*'),
            new ServerMediaType('*', '*'),
            0,
        ];

        yield [
            new ServerMediaType('*', '*'),
            new ServerMediaType('application', 'json'),
            0,
        ];

        yield [
            new ServerMediaType('application', 'json'),
            new ServerMediaType('*', '*'),
            0,
        ];

        yield [
            new ServerMediaType('image', '*'),
            new ServerMediaType('image', '*'),
            0,
        ];

        yield [
            new ServerMediaType('image', '*'),
            new ServerMediaType('image', 'webp'),
            0,
        ];

        yield [
            new ServerMediaType('image', 'webp'),
            new ServerMediaType('image', '*'),
            0,
        ];

        yield [
            new ServerMediaType('application', 'json'),
            new ServerMediaType('application', 'xml'),
            'application/json' <=> 'application/xml',
        ];

        yield [
            new ServerMediaType('application', 'xml'),
            new ServerMediaType('application', 'json'),
            'application/xml' <=> 'application/json',
        ];
    }
}
