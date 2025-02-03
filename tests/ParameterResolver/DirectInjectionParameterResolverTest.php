<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\ParameterResolver;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;
use stdClass;
use Sunrise\Http\Router\ParameterResolver\DirectInjectionParameterResolver;
use PHPUnit\Framework\TestCase;

final class DirectInjectionParameterResolverTest extends TestCase
{
    public function testResolveParameter(): void
    {
        $dependency = $this->createMock(ServerRequestInterface::class);
        $parameter = new ReflectionParameter(fn(ServerRequestInterface $p) => null, 'p');
        $arguments = (new DirectInjectionParameterResolver($dependency))->resolveParameter($parameter, null);
        $this->assertSame($dependency, $arguments->current());
    }

    public function testResolveParameterWithSubtype(): void
    {
        $dependency = $this->createMock(ServerRequestInterface::class);
        $parameter = new ReflectionParameter(fn(RequestInterface $p) => null, 'p');
        $arguments = (new DirectInjectionParameterResolver($dependency))->resolveParameter($parameter, null);
        $this->assertSame($dependency, $arguments->current());
    }

    public function testUnknownDependency(): void
    {
        $parameter = new ReflectionParameter(fn(ServerRequestInterface $p) => null, 'p');
        $arguments = (new DirectInjectionParameterResolver(new stdClass()))->resolveParameter($parameter, null);
        $this->assertFalse($arguments->valid());
    }

    public function testNonNamedParameterType(): void
    {
        $dependency = $this->createMock(ServerRequestInterface::class);
        $parameter = new ReflectionParameter(fn(ServerRequestInterface|RequestInterface $p) => null, 'p');
        $arguments = (new DirectInjectionParameterResolver($dependency))->resolveParameter($parameter, null);
        $this->assertFalse($arguments->valid());
    }

    public function testBuiltInParameterType(): void
    {
        $dependency = $this->createMock(ServerRequestInterface::class);
        $parameter = new ReflectionParameter(fn(int $p) => null, 'p');
        $arguments = (new DirectInjectionParameterResolver($dependency))->resolveParameter($parameter, null);
        $this->assertFalse($arguments->valid());
    }
}
