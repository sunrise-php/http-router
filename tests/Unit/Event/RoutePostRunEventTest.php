<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Event\RoutePostRunEvent;
use Sunrise\Http\Router\RouteInterface;

final class RoutePostRunEventTest extends TestCase
{
    public function testConstructor(): void
    {
        $route = $this->createMock(RouteInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $event = new RoutePostRunEvent($route, $request, $response);

        $this->assertSame($route, $event->getRoute());
        $this->assertSame($request, $event->getRequest());
        $this->assertSame($response, $event->getResponse());
    }

    public function testSetResponse(): void
    {
        $event = new RoutePostRunEvent(
            $this->createMock(RouteInterface::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ResponseInterface::class),
        );

        $response = $this->createMock(ResponseInterface::class);
        $event->setResponse($response);
        $this->assertSame($response, $event->getResponse());
    }
}
