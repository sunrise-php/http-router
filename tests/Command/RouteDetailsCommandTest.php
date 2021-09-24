<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Command;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Command\RouteDetailsCommand;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\Tests\Fixture;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * RouteDetailsCommandTest
 */
class RouteDetailsCommandTest extends TestCase
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

        // for code coverage...
        $routes[1]->setPath('/resource/{id<\d+>}');

        $router = new Router();
        $router->addRoute(...$routes);

        $command = new RouteDetailsCommand($router);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'route' => $routes[1]->getName(),
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * @return void
     */
    public function testRouteNotFound() : void
    {
        $router = new Router();

        $command = new RouteDetailsCommand($router);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'route' => 'foo',
        ]);

        $this->assertSame(1, $exitCode);
    }
}
