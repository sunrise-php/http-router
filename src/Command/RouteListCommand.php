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

use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\Router;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Sunrise\Http\Router\path_plain;

/**
 * This command will list all routes in your application
 *
 * If you can't pass the router to the constructor or your architecture has problems with the autowiring,
 * then just inherit this class and override the {@see self::getRouter()} method.
 *
 * @since 2.9.0
 */
class RouteListCommand extends Command
{

    /**
     * Constructor of the class
     *
     * @param Router|null $router
     */
    public function __construct(private ?Router $router = null)
    {
        parent::__construct();
    }

    /**
     * Gets the router instance populated with routes
     *
     * @return Router
     *
     * @throws LogicException
     *         If the command doesn't contain the router instance.
     *
     * @since 2.11.0
     */
    protected function getRouter(): Router
    {
        if (!isset($this->router)) {
            throw new LogicException(\sprintf(
                'The %2$s() method MUST return the %1$s class instance. ' .
                'Pass the %1$s class instance to the constructor or override the %2$s() method.',
                Router::class,
                __METHOD__,
            ));
        }

        return $this->router;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('router:route-list');
        $this->setDescription('Lists all routes in your application');
    }

    /**
     * @inheritDoc
     */
    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);

        $table->setHeaders([
            'Name',
            'Host',
            'Path',
            'Verb',
        ]);

        foreach ($this->getRouter()->getRoutes()->all() as $route) {
            $table->addRow([
                $route->getName(),
                $route->getHost() ?? '*',
                path_plain($route->getPath()),
                \join(', ', $route->getMethods()),
            ]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
