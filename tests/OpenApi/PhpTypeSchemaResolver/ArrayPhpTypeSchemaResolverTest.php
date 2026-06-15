<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi\PhpTypeSchemaResolver;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\ArrayPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeInterface;
use Sunrise\Http\Router\Tests\Fixture\Tests\PhpDocArrayFixture;
use Sunrise\Http\Router\Tests\Fixture\Tests\PhpDocArrayItemFixture;

final class ArrayPhpTypeSchemaResolverTest extends TestCase
{
    private OpenApiPhpTypeSchemaResolverManagerInterface&MockObject $mockedOpenApiPhpTypeSchemaResolverManager;

    protected function setUp(): void
    {
        $this->mockedOpenApiPhpTypeSchemaResolverManager = $this
            ->createMock(OpenApiPhpTypeSchemaResolverManagerInterface::class);
    }

    public function testResolvePhpTypeSchema(): void
    {
        $this->mockedOpenApiPhpTypeSchemaResolverManager->expects(self::never())->method('resolvePhpTypeSchema');
        $property = $this->createProperty('untypedItems');
        $phpTypeSchema = $this->createResolver()->resolvePhpTypeSchema(new Type('array'), $property);
        self::assertSame(['type' => 'array'], $phpTypeSchema);
    }

    public function testSubtype(): void
    {
        $property = $this->createProperty('subtypedItems');
        $this->mockItemType($property, 'string', ['type' => 'string']);
        $phpTypeSchema = $this->createResolver()->resolvePhpTypeSchema(new Type('array'), $property);
        self::assertSame([
            'type' => 'array',
            'items' => ['type' => 'string'],
            'maxItems' => 2,
        ], $phpTypeSchema);
    }

    public function testVar(): void
    {
        $property = $this->createProperty('items');
        $this->mockItemType($property, PhpDocArrayItemFixture::class);
        $phpTypeSchema = $this->createResolver()->resolvePhpTypeSchema(new Type('array'), $property);
        self::assertSame([
            'type' => 'array',
            'items' => ['type' => 'object'],
        ], $phpTypeSchema);
    }

    public function testListVar(): void
    {
        $property = $this->createProperty('listItems');
        $this->mockItemType($property, PhpDocArrayItemFixture::class);
        $phpTypeSchema = $this->createResolver()->resolvePhpTypeSchema(new Type('array'), $property);
        self::assertSame([
            'type' => 'array',
            'items' => ['type' => 'object'],
        ], $phpTypeSchema);
    }

    public function testNullableVar(): void
    {
        $property = $this->createProperty('nullableItems');
        $this->mockItemType($property, PhpDocArrayItemFixture::class, allowsNull: true);
        $phpTypeSchema = $this->createResolver()->resolvePhpTypeSchema(new Type('array'), $property);
        self::assertSame([
            'type' => 'array',
            'items' => ['type' => 'object'],
        ], $phpTypeSchema);
    }

    public function testNullableArrayVar(): void
    {
        $property = $this->createProperty('nullableArray');
        $this->mockItemType($property, PhpDocArrayItemFixture::class);
        $phpTypeSchema = $this->createResolver()->resolvePhpTypeSchema(new Type('array'), $property);
        self::assertSame([
            'type' => 'array',
            'items' => ['type' => 'object'],
        ], $phpTypeSchema);
    }

    public function testMixedVar(): void
    {
        $property = $this->createProperty('mixedItems');
        $phpTypeSchema = $this->createResolver()->resolvePhpTypeSchema(new Type('array'), $property);
        self::assertSame(['type' => 'array', 'items' => []], $phpTypeSchema);
    }

    public function testCompoundVar(): void
    {
        $property = $this->createProperty('compoundItems');
        $phpTypeSchema = $this->createResolver()->resolvePhpTypeSchema(new Type('array'), $property);
        self::assertSame(['type' => 'array', 'items' => []], $phpTypeSchema);
    }

    public function testUnsupportedPhpType(): void
    {
        $this->expectException(UnsupportedPhpTypeException::class);
        $this->createResolver()->resolvePhpTypeSchema(new Type('string'), $this->createProperty('items'));
    }

    public function testWeight(): void
    {
        self::assertSame(0, $this->createResolver()->getWeight());
    }

    private function createResolver(): ArrayPhpTypeSchemaResolver
    {
        $resolver = new ArrayPhpTypeSchemaResolver();
        $resolver->setOpenApiPhpTypeSchemaResolverManager($this->mockedOpenApiPhpTypeSchemaResolverManager);
        return $resolver;
    }

    private function createProperty(string $name): ReflectionProperty
    {
        return new ReflectionProperty(PhpDocArrayFixture::class, $name);
    }

    /**
     * @param array<array-key, mixed> $phpTypeSchema
     */
    private function mockItemType(
        ReflectionProperty $property,
        string $name,
        array $phpTypeSchema = ['type' => 'object'],
        bool $allowsNull = false,
    ): void {
        $this->mockedOpenApiPhpTypeSchemaResolverManager
            ->expects(self::once())
            ->method('resolvePhpTypeSchema')
            ->with(self::callback(static fn(TypeInterface $type): bool => $type->getName() === $name && $type->allowsNull() === $allowsNull), $property)
            ->willReturn($phpTypeSchema);
    }
}
