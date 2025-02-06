<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\ResponseResolver;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use Sunrise\Http\Router\ResponseResolver\EmptyResponseResolver;

final class EmptyResponseResolverTest extends TestCase
{
    private ResponseFactoryInterface&MockObject $mockedResponseFactory;
    private ServerRequestInterface&MockObject $mockedServerRequest;

    protected function setUp(): void
    {
        $this->mockedResponseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
    }

    public function testResolveResponse(): void
    {
        $responder = new ReflectionMethod(new class
        {
            public function test(): void
            {
            }
        }, 'test');

        $expectedResponse = $this->createMock(ResponseInterface::class);
        $this->mockedResponseFactory->expects(self::once())->method('createResponse')->with(204)->willReturn($expectedResponse);
        $resolvedResponse = (new EmptyResponseResolver($this->mockedResponseFactory))->resolveResponse(null, $responder, $this->mockedServerRequest);
        $this->assertSame($expectedResponse, $resolvedResponse);
    }

    public function testUnsupportedResponse(): void
    {
        $responder = new ReflectionMethod(new class
        {
            public function test(): void
            {
            }
        }, 'test');

        $this->mockedResponseFactory->expects(self::never())->method('createResponse');
        $this->assertNull((new EmptyResponseResolver($this->mockedResponseFactory))->resolveResponse(0, $responder, $this->mockedServerRequest));
    }

    public function testWeight(): void
    {
        $this->assertSame(0, (new EmptyResponseResolver($this->mockedResponseFactory))->getWeight());
    }
}
