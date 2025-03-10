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
    private ServerRequestInterface&MockObject $mockedRequest;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    public function testHandle(): void
    {
        $callback = function (ServerRequestInterface $request): ResponseInterface {
            self::assertSame($this->mockedRequest, $request);
            return $this->mockedResponse;
        };

        self::assertSame($this->mockedResponse, (new CallableRequestHandler($callback))->handle($this->mockedRequest));
    }
}
