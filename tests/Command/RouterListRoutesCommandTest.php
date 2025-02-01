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
    private RouterInterface&MockObject $mockedRouter;
    private CommandTester $commandTester;

    /** @var array<array-key, RouteInterface&MockObject> */
    private array $mockedRoutes = [];

    protected function setUp(): void
    {
        $this->mockedRouter = $this->createMock(RouterInterface::class);
        $this->commandTester = new CommandTester(new RouterListRoutesCommand($this->mockedRouter));
        $this->mockedRoutes = [];
    }

    public function testExecuteWithRoutes(): void
    {
        $this->mockRoute();

        $this->mockedRouter
            ->expects(self::once())
            ->method('getRoutes')
            ->willReturn($this->mockedRoutes);

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
    }

    public function testExecuteWithoutRoutes(): void
    {
        $this->mockedRouter
            ->expects(self::once())
            ->method('getRoutes')
            ->willReturn([]);

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
    }

    private function mockRoute(): RouteInterface&MockObject
    {
        return $this->mockedRoutes[] = $this->createMock(RouteInterface::class);
    }
}
