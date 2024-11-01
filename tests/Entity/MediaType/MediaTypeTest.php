<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\MediaType;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\MediaType\MediaType;

final class MediaTypeTest extends TestCase
{
    public function testConstructorWithOptionalParameters(): void
    {
        $mediaType = new MediaType('application', 'json', ['q' => '1.0']);
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
        $this->assertSame('application/json', $mediaType->getIdentifier());
        $this->assertSame(['q' => '1.0'], $mediaType->getParameters());
    }

    public function testConstructorWithoutOptionalParameters(): void
    {
        $mediaType = new MediaType('application', 'json');
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
        $this->assertSame('application/json', $mediaType->getIdentifier());
        $this->assertSame([], $mediaType->getParameters());
    }

    public function testToString(): void
    {
        $mediaType = new MediaType('application', 'json', ['q' => '1.0']);
        $this->assertSame('application/json', (string) $mediaType);
    }
}
