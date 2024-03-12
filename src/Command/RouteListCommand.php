<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\Command;

use Sunrise\Http\Router\Router;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function join;

/**
 * @since 2.9.0
 */
#[AsCommand('router:route-list', 'Lists all routes.')]
final class RouteListCommand extends Command
{
    public function __construct(private readonly Router $router)
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);

        $table->setHeaders([
            'Name',
            'Path',
            'Methods',
        ]);

        foreach ($this->router->getRoutes() as $route) {
            $table->addRow([
                $route->getName(),
                $route->getPath(),
                $route->getMethods() === [] ? '*' : join(', ', $route->getMethods()),
            ]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
