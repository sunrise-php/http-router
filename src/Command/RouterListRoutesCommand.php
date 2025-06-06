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

use Sunrise\Http\Router\RouterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function implode;

/**
 * @since 2.9.0
 */
#[AsCommand('router:list-routes', 'Lists routes.')]
final class RouterListRoutesCommand extends Command
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);

        $table->setHeaders([
            'NAME',
            'PATH',
            'METHOD(S)',
        ]);

        foreach ($this->router->getRoutes() as $route) {
            $table->addRow([
                $route->getName(),
                $route->getPath(),
                implode(', ', $route->getMethods()),
            ]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
