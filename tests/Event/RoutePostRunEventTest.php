<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Event;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Event\RoutePostRunEvent;
use Sunrise\Http\Router\RouteInterface;

final class RoutePostRunEventTest extends TestCase
{
    private RouteInterface&MockObject $mockedRoute;
    private ServerRequestInterface&MockObject $mockedServerRequest;
    private ResponseInterface&MockObject $mockedResponse;

    protected function setUp(): void
    {
        $this->mockedRoute = $this->createMock(RouteInterface::class);
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
    }

    public function testConstructor(): void
    {
        $event = new RoutePostRunEvent(
            $this->mockedRoute,
            $this->mockedServerRequest,
            $this->mockedResponse,
        );

        $this->assertSame($this->mockedRoute, $event->route);
        $this->assertSame($this->mockedServerRequest, $event->request);
        $this->assertSame($this->mockedResponse, $event->response);
    }
}
