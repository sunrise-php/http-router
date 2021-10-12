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

        $this->assertSame('router:route-list', $command->getName());

        $commandTester = new CommandTester($command);

        $this->assertSame(0, $commandTester->execute([]));
    }

    /**
     * @return void
     */
    public function testRunInheritedCommand() : void
    {
        $command = new class extends RouteListCommand
        {
            public function __construct()
            {
                parent::__construct(null);
            }

            protected function getRouter() : Router
            {
                return new Router();
            }
        };

        $this->assertSame('router:route-list', $command->getName());

        $commandTester = new CommandTester($command);

        $this->assertSame(0, $commandTester->execute([]));
    }

    /**
     * @return void
     */
    public function testRunRenamedCommand() : void
    {
        $command = new class extends RouteListCommand
        {
            protected static $defaultName = 'foo';
            protected static $defaultDescription = 'bar';

            public function __construct()
            {
                parent::__construct(new Router());
            }
        };

        $this->assertSame('foo', $command->getName());
        $this->assertSame('bar', $command->getDescription());

        $commandTester = new CommandTester($command);

        $this->assertSame(0, $commandTester->execute([]));
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
}
