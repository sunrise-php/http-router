<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\ParameterResolver;

use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolver\DefaultValueParameterResolver;
use PHPUnit\Framework\TestCase;

final class DefaultValueParameterResolverTest extends TestCase
{
    public function testResolveParameter(): void
    {
        $parameter = new ReflectionParameter(fn($p = 1) => null, 'p');
        $arguments = (new DefaultValueParameterResolver())->resolveParameter($parameter, null);
        self::assertSame(1, $arguments->current());
    }

    public function testResolveParameterWithoutDefaultValue(): void
    {
        $parameter = new ReflectionParameter(fn($p) => null, 'p');
        $arguments = (new DefaultValueParameterResolver())->resolveParameter($parameter, null);
        self::assertFalse($arguments->valid());
    }

    public function testWeight(): void
    {
        self::assertSame(-1000, (new DefaultValueParameterResolver())->getWeight());
    }
}
