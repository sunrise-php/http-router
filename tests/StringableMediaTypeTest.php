<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\MediaTypeInterface;
use Sunrise\Http\Router\StringableMediaType;

final class StringableMediaTypeTest extends TestCase
{
    public function testStringableMediaType(): void
    {
        $mediaType = $this->createMock(MediaTypeInterface::class);
        $mediaType->expects(self::once())->method('getIdentifier')->willReturn('application/json');
        $this->assertSame('application/json', (string) StringableMediaType::create($mediaType));
    }
}
