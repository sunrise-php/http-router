<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Command\RouterClearCacheCommand;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Dictionary\CacheKey;
use Symfony\Component\Console\Tester\CommandTester;

final class RouterClearCacheCommandTest extends TestCase
{
    private CacheInterface&MockObject $cacheMock;

    protected function setUp(): void
    {
        $this->cacheMock = $this->createMock(CacheInterface::class);
    }

    public function testExecuteWithoutCache(): void
    {
        $command = new RouterClearCacheCommand(null);
        $commandTester = new CommandTester($command);
        $this->assertSame(0, $commandTester->execute([]));
    }

    public function testExecuteWithCache(): void
    {
        $this->cacheMock->expects($this->once())->method('delete')->with(CacheKey::DESCRIPTORS);

        $command = new RouterClearCacheCommand($this->cacheMock);
        $commandTester = new CommandTester($command);
        $this->assertSame(0, $commandTester->execute([]));
    }
}
