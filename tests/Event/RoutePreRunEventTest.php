<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Event;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Event\RoutePreRunEvent;
use Sunrise\Http\Router\RouteInterface;

final class RoutePreRunEventTest extends TestCase
{
    private RouteInterface&MockObject $routeMock;
    private ServerRequestInterface&MockObject $serverRequestMock;

    protected function setUp(): void
    {
        $this->routeMock = $this->createMock(RouteInterface::class);
        $this->serverRequestMock = $this->createMock(ServerRequestInterface::class);
    }

    public function testConstructor(): void
    {
        $event = new RoutePreRunEvent($this->routeMock, $this->serverRequestMock);
        $this->assertSame($this->routeMock, $event->getRoute());
        $this->assertSame($this->serverRequestMock, $event->getRequest());
    }

    public function testSetRequest(): void
    {
        $event = new RoutePreRunEvent($this->routeMock, $this->serverRequestMock);
        $event->setRequest($newServerRequest = clone $this->serverRequestMock);
        $this->assertSame($newServerRequest, $event->getRequest());
    }
}
