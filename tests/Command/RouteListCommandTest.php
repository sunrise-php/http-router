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
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $router = new Router();
        $router->addRoute(...$routes);

        // @codingStandardsIgnoreStart
        $command = new CommandTester(new class ($router) extends RouteListCommand {
            private $router;

            public function __construct(Router $router) {
                parent::__construct();

                $this->router = $router;
            }

            protected function getRouter() : Router {
                return $this->router;
            }
        });
        // @codingStandardsIgnoreEnd

        $exitCode = $command->execute([]);

        $this->assertSame(0, $exitCode);
    }
}
