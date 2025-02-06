<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use Generator;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolverChain;
use Sunrise\Http\Router\ParameterResolverInterface;

final class ParameterResolverChainTest extends TestCase
{
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

        $this->mockParameterResolver('bar', value: '2', calls: 2, weight: 2);
        $this->mockParameterResolver('qux', value: '4', calls: 3, weight: 4);
        $this->mockParameterResolver('foo', value: '1', calls: 1, weight: 1);
        $this->mockParameterResolver('baz', value: '3', calls: 3, weight: 3);

        $this->assertSame(['1', '2', '3'], [...(new ParameterResolverChain($this->mockedParameterResolvers))->resolveParameters($foo, $bar, $baz)]);
    }

    public function testWithContext(): void
    {
        $context = $this->createMock(ServerRequestInterface::class);

        $foo = new ReflectionParameter(static fn($foo) => null, 'foo');
        $bar = new ReflectionParameter(static fn($bar) => null, 'bar');
        $baz = new ReflectionParameter(static fn($baz) => null, 'baz');

        $this->mockParameterResolver('bar', value: '2', calls: 2, weight: 2, context: $context);
        $this->mockParameterResolver('qux', value: '4', calls: 3, weight: 4, context: $context);
        $this->mockParameterResolver('foo', value: '1', calls: 1, weight: 1, context: $context);
        $this->mockParameterResolver('baz', value: '3', calls: 3, weight: 3, context: $context);

        $resolverChain = new ParameterResolverChain($this->mockedParameterResolvers);
        $resolverChainCopy = $resolverChain->withContext($context);

        $this->assertNotSame($resolverChainCopy, $resolverChain);
        $this->assertSame(['1', '2', '3'], [...$resolverChainCopy->resolveParameters($foo, $bar, $baz)]);
    }

    public function testWithResolver(): void
    {
        $foo = new ReflectionParameter(static fn($foo) => null, 'foo');
        $bar = new ReflectionParameter(static fn($bar) => null, 'bar');
        $baz = new ReflectionParameter(static fn($baz) => null, 'baz');

        $this->mockParameterResolver('bar', value: '2', calls: 1, weight: 20);
        $this->mockParameterResolver('qux', value: '4', calls: 3, weight: 40);
        $this->mockParameterResolver('foo', value: '1', calls: 1, weight: 10);
        $this->mockParameterResolver('baz', value: '3', calls: 2, weight: 30);

        $resolverChain = new ParameterResolverChain($this->mockedParameterResolvers);
        $resolverChainCopy = $resolverChain->withResolver(
            $this->mockParameterResolver('baz', value: '33', calls: 3, weight: 35, register: false),
            $this->mockParameterResolver('bar', value: '22', calls: 2, weight: 25, register: false),
        );

        $this->assertNotSame($resolverChainCopy, $resolverChain);
        $this->assertSame(['1', '22', '33'], [...$resolverChainCopy->resolveParameters($foo, $bar, $baz)]);
    }

    public function testResolveUnsupportedParameter(): void
    {
        $resolverChain = new ParameterResolverChain($this->mockedParameterResolvers);
        $baz = new ReflectionParameter(\abs(...), 'num');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/is not supported/');
        $resolverChain->resolveParameters($baz)->rewind();
    }

    private function mockParameterResolver(string $name, string $value, int $calls = 1, int $weight = 0, mixed $context = null, bool $register = true): ParameterResolverInterface&MockObject
    {
        $resolver = $this->createMock(ParameterResolverInterface::class);
        $callback = static fn(ReflectionParameter $p): Generator => $p->name === $name ? yield $value : null;
        $resolver->expects(self::exactly($calls))->method('resolveParameter')->with(self::anything(), $context)->willReturnCallback($callback);
        $resolver->method('getWeight')->willReturn($weight);

        if ($register) {
            $this->mockedParameterResolvers[] = $resolver;
        }

        return $resolver;
    }
}
