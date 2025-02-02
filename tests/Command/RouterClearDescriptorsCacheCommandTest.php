<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouterClearDescriptorsCacheCommand;
use Sunrise\Http\Router\Loader\DescriptorLoaderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class RouterClearDescriptorsCacheCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $descriptorLoader = $this->createMock(DescriptorLoaderInterface::class);
        $command = new RouterClearDescriptorsCacheCommand($descriptorLoader);
        $commandTester = new CommandTester($command);
        $descriptorLoader->expects(self::once())->method('clearCache');
        $this->assertSame(Command::SUCCESS, $commandTester->execute([]));
    }
}
