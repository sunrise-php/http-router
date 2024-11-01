<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouterListRoutesCommand;
use Sunrise\Http\Router\RouterInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class RouterListRoutesCommandTest extends TestCase
{
    private RouterInterface&MockObject $routerMock;

    protected function setUp(): void
    {
        $this->routerMock = $this->createMock(RouterInterface::class);
    }

    public function testExecute(): void
    {
        $this->routerMock->expects($this->once())->method('getRoutes')->willReturn([]);

        $command = new RouterListRoutesCommand($this->routerMock);
        $commandTester = new CommandTester($command);
        $this->assertSame(0, $commandTester->execute([]));
    }
}
