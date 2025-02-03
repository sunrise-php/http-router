<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\RequestHandler;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use PHPUnit\Framework\TestCase;

final class CallableRequestHandlerTest extends TestCase
{
    private ServerRequestInterface&MockObject $mockedServerRequest;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    public function testHandle(): void
    {
        $callback = function (ServerRequestInterface $request): ResponseInterface {
            $this->assertSame($this->mockedServerRequest, $request);
            return $this->mockedResponse;
        };

        $this->assertSame($this->mockedResponse, (new CallableRequestHandler($callback))->handle($this->mockedServerRequest));
    }
}
