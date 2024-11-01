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
    private RouteInterface&MockObject $routeMock;
    private ServerRequestInterface&MockObject $serverRequestMock;
    private ResponseInterface&MockObject $responseMock;

    protected function setUp(): void
    {
        $this->routeMock = $this->createMock(RouteInterface::class);
        $this->serverRequestMock = $this->createMock(ServerRequestInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
    }

    public function testConstructor(): void
    {
        $event = new RoutePostRunEvent($this->routeMock, $this->serverRequestMock, $this->responseMock);
        $this->assertSame($this->routeMock, $event->getRoute());
        $this->assertSame($this->serverRequestMock, $event->getRequest());
        $this->assertSame($this->responseMock, $event->getResponse());
    }

    public function testSetResponse(): void
    {
        $event = new RoutePostRunEvent($this->routeMock, $this->serverRequestMock, $this->responseMock);
        $event->setResponse($newResponse = clone $this->responseMock);
        $this->assertSame($newResponse, $event->getResponse());
    }
}
