<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Command\RouterClearDescriptorsCacheCommand;
use Sunrise\Http\Router\Loader\DescriptorLoader;
use Symfony\Component\Console\Tester\CommandTester;

final class RouterClearCacheCommandTest extends TestCase
{
    private CacheInterface&MockObject $cacheMock;

    protected function setUp(): void
    {
        $this->cacheMock = $this->createMock(CacheInterface::class);
    }

    public function testExecuteWithCache(): void
    {
        $this->cacheMock->expects(self::once())
            ->method('delete')
            ->with(DescriptorLoader::DESCRIPTORS_CACHE_KEY);

        $descriptorLoader = new DescriptorLoader([], $this->cacheMock);
        $command = new RouterClearDescriptorsCacheCommand($descriptorLoader);
        $commandTester = new CommandTester($command);
        $this->assertSame(0, $commandTester->execute([]));
    }

    public function testExecuteWithoutCache(): void
    {
        $descriptorLoader = new DescriptorLoader([]);
        $command = new RouterClearDescriptorsCacheCommand($descriptorLoader);
        $commandTester = new CommandTester($command);
        $this->assertSame(0, $commandTester->execute([]));
    }
}
