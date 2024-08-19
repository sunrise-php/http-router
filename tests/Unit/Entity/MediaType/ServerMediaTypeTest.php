<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Unit\Entity\MediaType;

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

    /**
     * @dataProvider fromStringProvider
     */
    public function testFromString(string $string, string $expectedType, string $expectedSubtype): void
    {
        $mediaType = ServerMediaType::fromString($string);

        $this->assertSame($expectedType, $mediaType->getType());
        $this->assertSame($expectedSubtype, $mediaType->getSubtype());
    }

    public function testToString(): void
    {
        $mediaType = new ServerMediaType('application', 'json');

        $this->assertSame('application/json', (string) $mediaType);
    }

    private function fromStringProvider(): iterable
    {
        yield [
            '',
            '*',
            '*',
        ];

        yield [
            '*',
            '*',
            '*',
        ];

        yield [
            '*/*',
            '*',
            '*',
        ];

        yield [
            'image',
            'image',
            '*',
        ];

        yield [
            'application/json',
            'application',
            'json',
        ];
    }
}
