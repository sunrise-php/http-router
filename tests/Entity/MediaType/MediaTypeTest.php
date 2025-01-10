<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\MediaType;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\MediaType\MediaType;

final class MediaTypeTest extends TestCase
{
    public function testConstructor(): void
    {
        $mediaType = new MediaType('application/json');
        $this->assertSame('application/json', $mediaType->getIdentifier());
    }
}
