<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolverChain;
use Sunrise\Http\Router\ParameterResolverInterface;

final class ParameterResolverChainTest extends TestCase
{
    use TestKit;

    /**
     * @var list<ParameterResolverInterface&MockObject>
     */
    private array $mockedParameterResolvers;

    protected function setUp(): void
    {
        $this->mockedParameterResolvers = [];
    }

    public function testResolveParameters(): void
    {
        $foo = new ReflectionParameter(static fn($foo) => null, 'foo');
        $bar = new ReflectionParameter(static fn($bar) => null, 'bar');
        $baz = new ReflectionParameter(static fn($baz) => null, 'baz');

        $this->mockParameterResolver('bar', value: '2', weight: 2, calls: 2, registry: $this->mockedParameterResolvers);
        $this->mockParameterResolver('qux', value: '4', weight: 4, calls: 3, registry: $this->mockedParameterResolvers);
        $this->mockParameterResolver('foo', value: '1', weight: 1, calls: 1, registry: $this->mockedParameterResolvers);
        $this->mockParameterResolver('baz', value: '3', weight: 3, calls: 3, registry: $this->mockedParameterResolvers);

        self::assertSame(['1', '2', '3'], [...(new ParameterResolverChain($this->mockedParameterResolvers))->resolveParameters($foo, $bar, $baz)]);
    }

    public function testWithContext(): void
    {
        $context = $this->createMock(ServerRequestInterface::class);

        $foo = new ReflectionParameter(static fn($foo) => null, 'foo');
        $bar = new ReflectionParameter(static fn($bar) => null, 'bar');
        $baz = new ReflectionParameter(static fn($baz) => null, 'baz');

        $this->mockParameterResolver('bar', value: '2', context: $context, weight: 2, calls: 2, registry: $this->mockedParameterResolvers);
        $this->mockParameterResolver('qux', value: '4', context: $context, weight: 4, calls: 3, registry: $this->mockedParameterResolvers);
        $this->mockParameterResolver('foo', value: '1', context: $context, weight: 1, calls: 1, registry: $this->mockedParameterResolvers);
        $this->mockParameterResolver('baz', value: '3', context: $context, weight: 3, calls: 3, registry: $this->mockedParameterResolvers);

        $resolverChain = new ParameterResolverChain($this->mockedParameterResolvers);
        $resolverChainCopy = $resolverChain->withContext($context);

        self::assertNotSame($resolverChainCopy, $resolverChain);
        self::assertSame(['1', '2', '3'], [...$resolverChainCopy->resolveParameters($foo, $bar, $baz)]);
    }

    public function testWithResolver(): void
    {
        $foo = new ReflectionParameter(static fn($foo) => null, 'foo');
        $bar = new ReflectionParameter(static fn($bar) => null, 'bar');
        $baz = new ReflectionParameter(static fn($baz) => null, 'baz');

        $this->mockParameterResolver('bar', value: '2', weight: 20, calls: 1, registry: $this->mockedParameterResolvers);
        $this->mockParameterResolver('qux', value: '4', weight: 40, calls: 3, registry: $this->mockedParameterResolvers);
        $this->mockParameterResolver('foo', value: '1', weight: 10, calls: 1, registry: $this->mockedParameterResolvers);
        $this->mockParameterResolver('baz', value: '3', weight: 30, calls: 2, registry: $this->mockedParameterResolvers);

        $resolverChain = new ParameterResolverChain($this->mockedParameterResolvers);
        $resolverChainCopy = $resolverChain->withResolver(
            $this->mockParameterResolver('baz', value: '33', weight: 35, calls: 3),
            $this->mockParameterResolver('bar', value: '22', weight: 25, calls: 2),
        );

        self::assertNotSame($resolverChainCopy, $resolverChain);
        self::assertSame(['1', '22', '33'], [...$resolverChainCopy->resolveParameters($foo, $bar, $baz)]);
    }

    public function testResolveUnsupportedParameter(): void
    {
        $resolverChain = new ParameterResolverChain($this->mockedParameterResolvers);
        $baz = new ReflectionParameter(\abs(...), 'num');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/is not supported/');
        $resolverChain->resolveParameters($baz)->valid();
    }
}
