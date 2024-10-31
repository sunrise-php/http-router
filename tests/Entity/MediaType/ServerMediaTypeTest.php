<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\MediaType;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\MediaType\ServerMediaType;

final class ServerMediaTypeTest extends TestCase
{
    public function testConstructor(): void
    {
        $mediaType = new ServerMediaType('application', 'json');

        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
    }

    public function testToString(): void
    {
        $mediaType = new ServerMediaType('application', 'json');

        $this->assertSame('application/json', (string) $mediaType);
    }
}
