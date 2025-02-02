<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouterListRoutesCommand;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class RouterListRoutesCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $routes = [$this->createMock(RouteInterface::class)];
        $router->expects(self::once())->method('getRoutes')->willReturn($routes);
        $command = new RouterListRoutesCommand($router);
        $commandTester = new CommandTester($command);
        $this->assertSame(Command::SUCCESS, $commandTester->execute([]));
    }
}
