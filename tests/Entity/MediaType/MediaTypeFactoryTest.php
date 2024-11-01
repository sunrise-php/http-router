<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Entity\MediaType;

use Sunrise\Http\Router\Entity\MediaType\MediaTypeFactory;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;

final class MediaTypeFactoryTest extends TestCase
{
    public function testCreateWithObject(): void
    {
        $mediaTypeMock = $this->createMock(MediaTypeInterface::class);
        $this->assertSame($mediaTypeMock, MediaTypeFactory::create($mediaTypeMock));
    }

    public function testCreateWithValidString(): void
    {
        $mediaType = MediaTypeFactory::create('application/json');
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
    }

    public function testFromValidString(): void
    {
        $mediaType = MediaTypeFactory::fromString('application/json');
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
    }

    public function testAny(): void
    {
        $mediaType = MediaTypeFactory::any();
        $this->assertSame('*', $mediaType->getType());
        $this->assertSame('*', $mediaType->getSubtype());
    }

    public function testJson(): void
    {
        $mediaType = MediaTypeFactory::json();
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('json', $mediaType->getSubtype());
    }

    public function testXml(): void
    {
        $mediaType = MediaTypeFactory::xml();
        $this->assertSame('application', $mediaType->getType());
        $this->assertSame('xml', $mediaType->getSubtype());
    }

    public function testHtml(): void
    {
        $mediaType = MediaTypeFactory::html();
        $this->assertSame('text', $mediaType->getType());
        $this->assertSame('html', $mediaType->getSubtype());
    }

    public function testText(): void
    {
        $mediaType = MediaTypeFactory::text();
        $this->assertSame('text', $mediaType->getType());
        $this->assertSame('plain', $mediaType->getSubtype());
    }

    public function testImage(): void
    {
        $mediaType = MediaTypeFactory::image();
        $this->assertSame('image', $mediaType->getType());
        $this->assertSame('*', $mediaType->getSubtype());
    }
}
