<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Command;

/**
 * Import classes
 */
use Sunrise\Http\Router\Router;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_plain;
use function join;

/**
 * This command displays a list of routes
 *
 * @since 2.9.0
 */
abstract class RouteListCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name ?? 'router:route-list');
    }

    /**
     * Gets the router filled with routes
     *
     * @return Router
     */
    abstract protected function getRouter() : Router;

    /**
     * {@inheritdoc}
     */
    final protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $table = new Table($output);

        $table->setHeaders([
            'Name',
            'Host',
            'Path',
            'Verb',
        ]);

        foreach ($this->getRouter()->getRoutes() as $route) {
            $table->addRow([
                $route->getName(),
                $route->getHost() ?? 'ANY',
                path_plain($route->getPath()),
                join(', ', $route->getMethods()),
            ]);
        }

        $table->render();

        return 0;
    }
}
