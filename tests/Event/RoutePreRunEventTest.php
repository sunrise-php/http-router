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
    private RouteInterface&MockObject $mockedRoute;
    private ServerRequestInterface&MockObject $mockedServerRequest;

    protected function setUp(): void
    {
        $this->mockedRoute = $this->createMock(RouteInterface::class);
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
    }

    public function testConstructor(): void
    {
        $event = new RoutePreRunEvent(
            $this->mockedRoute,
            $this->mockedServerRequest,
        );

        $this->assertSame($this->mockedRoute, $event->route);
        $this->assertSame($this->mockedServerRequest, $event->request);
    }
}
