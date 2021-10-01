<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Command;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouteListCommand;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\Test\Fixture;
use Symfony\Component\Console\Tester\CommandTester;

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
        $routes = [
            new Fixture\Route(),
            new Fixture\Route(),
            new Fixture\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $command = new RouteListCommand($router);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([]);

        $this->assertSame(0, $exitCode);
    }
}
