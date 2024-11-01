<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\MediaType;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\MediaType\ServerMediaType;

final class ServerMediaTypeTest extends TestCase
{
    public function testConstructor(): void
    {
        $serverMediaType = new ServerMediaType('application', 'json');
        $this->assertSame('application', $serverMediaType->getType());
        $this->assertSame('json', $serverMediaType->getSubtype());
    }

    public function testToString(): void
    {
        $serverMediaType = new ServerMediaType('application', 'json');
        $this->assertSame('application/json', (string) $serverMediaType);
    }
}
