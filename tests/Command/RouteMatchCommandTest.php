<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouteMatchCommand;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\Tests\Fixture;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * RouteMatchCommandTest
 */
class RouteMatchCommandTest extends TestCase
{

    /**
     * @return void
     */
    public function testExecute() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        $command = new RouteMatchCommand($router);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'route' => $routes[1]->getName(),
            'path' => $routes[1]->getPath(),
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * @return void
     */
    public function testRouteNotFound() : void
    {
        $router = new Router();

        $command = new RouteMatchCommand($router);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'route' => 'foo',
            'path' => 'bar',
        ]);

        $this->assertSame(1, $exitCode);
    }

    /**
     * @return void
     */
    public function testPathNotMatches() : void
    {
        $route = new Fixture\TestRoute();

        $router = new Router();
        $router->addRoute($route);

        $command = new RouteMatchCommand($router);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'route' => $route->getName(),
            'path' => '/',
        ]);

        $this->assertSame(1, $exitCode);
    }
}
