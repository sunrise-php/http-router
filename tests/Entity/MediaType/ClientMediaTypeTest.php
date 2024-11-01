<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\MediaType;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\MediaType\ClientMediaType;

final class ClientMediaTypeTest extends TestCase
{
    public function testConstructor(): void
    {
        $clientMediaType = new ClientMediaType('application', 'json', ['q' => '1.0']);
        $this->assertSame('application', $clientMediaType->getType());
        $this->assertSame('json', $clientMediaType->getSubtype());
        $this->assertSame(['q' => '1.0'], $clientMediaType->getParameters());
    }

    public function testToString(): void
    {
        $clientMediaType = new ClientMediaType('application', 'json', ['q' => '1.0']);
        $this->assertSame('application/json', (string) $clientMediaType);
    }
}
