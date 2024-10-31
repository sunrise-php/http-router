<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Event;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Event\RoutePreRunEvent;
use Sunrise\Http\Router\RouteInterface;

final class RoutePreRunEventTest extends TestCase
{
    public function testConstructor(): void
    {
        $route = $this->createMock(RouteInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $event = new RoutePreRunEvent($route, $request);

        $this->assertSame($route, $event->getRoute());
        $this->assertSame($request, $event->getRequest());
    }

    public function testSetRequest(): void
    {
        $event = new RoutePreRunEvent(
            $this->createMock(RouteInterface::class),
            $this->createMock(ServerRequestInterface::class),
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $event->setRequest($request);
        $this->assertSame($request, $event->getRequest());
    }
}
