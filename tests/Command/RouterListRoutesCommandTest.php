<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouterListRoutesCommand;
use Sunrise\Http\Router\RouterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class RouterListRoutesCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $commandTester = new CommandTester(new RouterListRoutesCommand($router));
        $this->assertSame(Command::SUCCESS, $commandTester->execute([]));
    }
}
