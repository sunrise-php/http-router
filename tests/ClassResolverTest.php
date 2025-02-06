<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionParameter;
use ReflectionType;
use Sunrise\Http\Router\ClassResolver;
use Sunrise\Http\Router\ParameterResolverChainInterface;

final class ClassResolverTest extends TestCase
{
    private ParameterResolverChainInterface&MockObject $mockedParameterResolverChain;
    private ContainerInterface&MockObject $mockedContainer;

    protected function setUp(): void
    {
        $this->mockedParameterResolverChain = $this->createMock(ParameterResolverChainInterface::class);
        $this->mockedContainer = $this->createMock(ContainerInterface::class);
    }

    public function testResolveClass(): void
    {
        $testClass = new class ('', '')
        {
            public function __construct(
                public readonly string $foo,
                public readonly string $bar,
            ) {
            }
        };

        $parametersResolver = static fn(ReflectionParameter $foo, ReflectionParameter $bar): Generator => yield from [$foo->getName(), $bar->getName()];
        $this->mockedParameterResolverChain->expects(self::once())->method('resolveParameters')->willReturnCallback($parametersResolver);
        $resolvedClass = (new ClassResolver($this->mockedParameterResolverChain))->resolveClass($testClass::class);
        $this->assertSame('foo', $resolvedClass->foo);
        $this->assertSame('bar', $resolvedClass->bar);
    }

    public function testContainer(): void
    {
        $expectedClass = new class
        {
        };

        $this->mockedContainer->expects(self::once())->method('has')->with($expectedClass::class)->willReturn(true);
        $this->mockedContainer->expects(self::once())->method('get')->with($expectedClass::class)->willReturn($expectedClass);
        $this->mockedParameterResolverChain->expects(self::never())->method('resolveParameters');
        $resolvedClass = (new ClassResolver($this->mockedParameterResolverChain, $this->mockedContainer))->resolveClass($expectedClass::class);
        $this->assertSame($expectedClass, $resolvedClass);
    }

    public function testContainerUnknownClass(): void
    {
        $testClass = new class
        {
        };

        $this->mockedContainer->expects(self::once())->method('has')->with($testClass::class)->willReturn(false);
        $this->mockedContainer->expects(self::never())->method('get');
        $this->mockedParameterResolverChain->expects(self::once())->method('resolveParameters')->with(/* no parameters */)->willReturnCallback(static fn(): Generator => yield from []);
        $resolvedClass = (new ClassResolver($this->mockedParameterResolverChain, $this->mockedContainer))->resolveClass($testClass::class);
        $this->assertSame($testClass::class, $resolvedClass::class);
    }

    public function testCacheResult(): void
    {
        $testClass = new class
        {
        };

        $this->mockedParameterResolverChain->expects(self::once())->method('resolveParameters')->with()->willReturnCallback(static fn(): Generator => yield from []);
        $classResolver = new ClassResolver($this->mockedParameterResolverChain);
        $this->assertSame($classResolver->resolveClass($testClass::class), $classResolver->resolveClass($testClass::class));
    }

    public function testUnknownClass(): void
    {
        $this->mockedParameterResolverChain->expects(self::never())->method('resolveParameters');
        $classResolver = new ClassResolver($this->mockedParameterResolverChain);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/does not exist/');
        $classResolver->resolveClass('Undefined');
    }

    public function testUninstantiableClass(): void
    {
        $this->mockedParameterResolverChain->expects(self::never())->method('resolveParameters');
        $classResolver = new ClassResolver($this->mockedParameterResolverChain);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/is not instantiable/');
        $classResolver->resolveClass(ReflectionType::class);
    }
}
