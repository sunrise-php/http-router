<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouterClearDescriptorsCacheCommand;
use Sunrise\Http\Router\Loader\DescriptorLoaderInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class RouterClearCacheCommandTest extends TestCase
{
    private DescriptorLoaderInterface&MockObject $descriptorLoader;

    protected function setUp(): void
    {
        $this->descriptorLoader = $this->createMock(DescriptorLoaderInterface::class);
    }

    public function testExecute(): void
    {
        $this->descriptorLoader->expects(self::once())->method('clearCache');

        $command = new RouterClearDescriptorsCacheCommand($this->descriptorLoader);
        $commandTester = new CommandTester($command);
        $this->assertSame(0, $commandTester->execute([]));
    }
}
