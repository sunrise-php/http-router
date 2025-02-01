<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouterClearDescriptorsCacheCommand;
use Sunrise\Http\Router\Loader\DescriptorLoaderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class RouterClearDescriptorsCacheCommandTest extends TestCase
{
    private DescriptorLoaderInterface&MockObject $mockedDescriptorLoader;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->mockedDescriptorLoader = $this->createMock(DescriptorLoaderInterface::class);
        $this->commandTester = new CommandTester(new RouterClearDescriptorsCacheCommand($this->mockedDescriptorLoader));
    }

    public function testExecute(): void
    {
        $this->mockedDescriptorLoader
            ->expects(self::once())
            ->method('clearCache');

        $this->assertSame(Command::SUCCESS, $this->commandTester->execute([]));
    }
}
