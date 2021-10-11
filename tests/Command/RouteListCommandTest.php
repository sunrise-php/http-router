<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouteListCommand;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\Tests\Fixtures;
use Symfony\Component\Console\Tester\CommandTester;
use RuntimeException;

/**
 * RouteListCommandTest
 */
class RouteListCommandTest extends TestCase
{

    /**
     * @return void
     */
    public function testRun() : void
    {
        $router = new Router();

        $router->addRoute(...[
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ]);

        $command = new RouteListCommand($router);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * @return void
     */
    public function testRunWithoutRouter() : void
    {
        $command = new RouteListCommand();
        $commandTester = new CommandTester($command);

        $this->expectException(RuntimeException::class);

        $commandTester->execute([]);
    }

    /**
     * @return void
     */
    public function testRunUserCommand() : void
    {
        $userCommand = new class extends RouteListCommand
        {
            protected function getRouter() : Router
            {
                return new Router();
            }
        };

        $commandTester = new CommandTester($userCommand);

        $exitCode = $commandTester->execute([]);

        $this->assertSame(0, $exitCode);
    }
}
