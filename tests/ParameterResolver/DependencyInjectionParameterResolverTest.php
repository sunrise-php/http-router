<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\ParameterResolver;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolver\DependencyInjectionParameterResolver;
use PHPUnit\Framework\TestCase;

final class DependencyInjectionParameterResolverTest extends TestCase
{
    private ContainerInterface&MockObject $mockedContainer;

    protected function setUp(): void
    {
        $this->mockedContainer = $this->createMock(ContainerInterface::class);
    }

    public function testResolveParameter(): void
    {
        $parameter = new ReflectionParameter(fn(\foo $p) => null, 'p');
        $this->mockedContainer->expects(self::once())->method('has')->with('foo')->willReturn(true);
        $this->mockedContainer->expects(self::once())->method('get')->with('foo')->willReturn('bar');
        $arguments = (new DependencyInjectionParameterResolver($this->mockedContainer))->resolveParameter($parameter, null);
        $this->assertSame('bar', $arguments->current());
    }

    public function testUnknownDependency(): void
    {
        $parameter = new ReflectionParameter(fn(\foo $p) => null, 'p');
        $this->mockedContainer->expects(self::once())->method('has')->with('foo')->willReturn(false);
        $this->mockedContainer->expects(self::never())->method('get');
        $arguments = (new DependencyInjectionParameterResolver($this->mockedContainer))->resolveParameter($parameter, null);
        $this->assertFalse($arguments->valid());
    }

    public function testNonNamedParameterType(): void
    {
        $parameter = new ReflectionParameter(fn(\foo|\bar $p) => null, 'p');
        $this->mockedContainer->expects(self::never())->method('has');
        $this->mockedContainer->expects(self::never())->method('get');
        $arguments = (new DependencyInjectionParameterResolver($this->mockedContainer))->resolveParameter($parameter, null);
        $this->assertFalse($arguments->valid());
    }

    public function testBuiltInParameterType(): void
    {
        $parameter = new ReflectionParameter(fn(int $p) => null, 'p');
        $this->mockedContainer->expects(self::never())->method('has');
        $this->mockedContainer->expects(self::never())->method('get');
        $arguments = (new DependencyInjectionParameterResolver($this->mockedContainer))->resolveParameter($parameter, null);
        $this->assertFalse($arguments->valid());
    }
}
