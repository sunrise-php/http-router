<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Middleware\CallableMiddleware;
use PHPUnit\Framework\TestCase;

final class CallableMiddlewareTest extends TestCase
{
    private ServerRequestInterface&MockObject $mockedServerRequest;
    private RequestHandlerInterface&MockObject $mockedRequestHandler;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    public function testProcess(): void
    {
        $callback = function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
            $this->assertSame($this->mockedServerRequest, $request);
            $this->assertSame($this->mockedRequestHandler, $handler);
            return $this->mockedResponse;
        };

        $this->assertSame($this->mockedResponse, (new CallableMiddleware($callback))->process($this->mockedServerRequest, $this->mockedRequestHandler));
    }
}
