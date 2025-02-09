<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\StringableMediaType;

final class StringableMediaTypeTest extends TestCase
{
    use TestKit;

    public function testStringableMediaType(): void
    {
        self::assertSame('application/json', (string) StringableMediaType::create($this->mockMediaType('application/json', calls: 1)));
    }
}
