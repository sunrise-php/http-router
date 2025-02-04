<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\ParameterResolver;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\ParameterResolver\RequestCookieParameterResolver;
use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestCookieParameterResolverTest extends TestCase
{
    private ServerRequestInterface&MockObject $mockedServerRequest;
    private HydratorInterface&MockObject $mockedHydrator;
    private ValidatorInterface&MockObject $mockedValidator;

    protected function setUp(): void
    {
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedHydrator = $this->createMock(HydratorInterface::class);
        $this->mockedValidator = $this->createMock(ValidatorInterface::class);
    }

    public function testResolveParameter(): void
    {
        $this->mockedServerRequest->expects(self::exactly(2))->method('getCookieParams')->willReturn(['foo' => 'bar']);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willReturn('bar');
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->assertSame('bar', $arguments->current());
    }

    public function testUnsupportedContext(): void
    {
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, null);
        $this->assertFalse($arguments->valid());
    }

    public function testNonAnnotatedParameter(): void
    {
        $parameter = new ReflectionParameter(fn(string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->assertFalse($arguments->valid());
    }

    public function testDefaultParameterValue(): void
    {
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p = 'bar') => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->assertSame('bar', $arguments->current());
    }

    public function testMissingCookie(): void
    {
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(HttpException::class);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(400, $e->getCode());
            throw $e;
        }
    }
}
