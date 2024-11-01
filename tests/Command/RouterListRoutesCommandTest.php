<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouterListRoutesCommand;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouterInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class RouterListRoutesCommandTest extends TestCase
{
    private RouterInterface&MockObject $routerMock;

    /** @var array<array-key, RouteInterface&MockObject> */
    private array $routeMocks = [];

    protected function setUp(): void
    {
        $this->routerMock = $this->createMock(RouterInterface::class);
        $this->routeMocks = [];
    }

    public function testExecuteWithRoutes(): void
    {
        $this->mockRoute();
        $this->routerMock->expects($this->once())->method('getRoutes')->willReturn($this->routeMocks);

        $command = new RouterListRoutesCommand($this->routerMock);
        $commandTester = new CommandTester($command);
        $this->assertSame(0, $commandTester->execute([]));
    }

    public function testExecuteWithoutRoutes(): void
    {
        $this->routerMock->expects($this->once())->method('getRoutes')->willReturn($this->routeMocks);

        $command = new RouterListRoutesCommand($this->routerMock);
        $commandTester = new CommandTester($command);
        $this->assertSame(0, $commandTester->execute([]));
    }

    private function mockRoute(): RouteInterface&MockObject
    {
        return $this->routeMocks[] = $this->createMock(RouteInterface::class);
    }
}
