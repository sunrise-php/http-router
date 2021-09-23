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

/**
 * RouteListCommand
 *
 * @since 2.9.0
 */
final class RouteListCommand extends Command
{

    /**
     * @var Router
     */
    private $router;

    /**
     * {@inheritdoc}
     *
     * @param Router $router
     * @param null|string $name
     */
    public function __construct(Router $router, ?string $name = null)
    {
        $this->router = $router;

        parent::__construct($name ?? 'router:route-list');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $table = new Table($output);
        $table->setStyle('box');

        $table->setHeaders([
            'Name',
            'Host',
            'Verb',
            'Path',
        ]);

        foreach ($this->router->getRoutes() as $route) {
            $table->addRow([
                $route->getName(),
                $route->getHost() ?? 'ANY',
                \implode(', ', $route->getMethods()),
                path_plain($route->getPath()),
            ]);
        }

        $table->render();

        return 0;
    }
}
