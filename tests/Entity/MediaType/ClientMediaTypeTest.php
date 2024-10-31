<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\MediaType;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\MediaType\ClientMediaType;

final class ClientMediaTypeTest extends TestCase
{
    public function testConstructor(): void
    {
        $mediaType = new ClientMediaType('application', 'json', ['q' => '1.0']);

        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
        $this->assertSame(['q' => '1.0'], $mediaType->getParameters());
    }

    public function testToString(): void
    {
        $mediaType = new ClientMediaType('application', 'json', ['q' => '1.0']);

        $this->assertSame('application/json', (string) $mediaType);
    }
}
