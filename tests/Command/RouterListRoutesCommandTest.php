<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouterListRoutesCommand;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class RouterListRoutesCommandTest extends TestCase
{
    /** @var array<array-key, RouteInterface&MockObject> */
    private array $mockedRoutes;
    private RouterInterface&MockObject $mockedRouter;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->mockedRoutes = [];
        $this->mockedRouter = $this->createMock(RouterInterface::class);
        $this->mockedRouter->method('getRoutes')->willReturnCallback(fn(): array => $this->mockedRoutes);
        $this->commandTester = new CommandTester(new RouterListRoutesCommand($this->mockedRouter));
    }

    public function testExecuteWithRoutes(): void
    {
        $this->mockRoute();
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
    }

    public function testExecuteWithoutRoutes(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
    }

    private function mockRoute(): RouteInterface&MockObject
    {
        return $this->mockedRoutes[] = $this->createMock(RouteInterface::class);
    }
}
