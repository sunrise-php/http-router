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
use RuntimeException;
use Sunrise\Http\Router\Router;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Import functions
 */
use function join;
use function sprintf;
use function Sunrise\Http\Router\path_plain;

/**
 * This command will list all routes in your application
 *
 * If you cannot pass the router to the constructor
 * or your architecture has problems with autowiring,
 * then just inherit this class and override the getRouter method.
 *
 * @since 2.9.0
 */
class RouteListCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'router:route-list';

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected static $defaultDescription = 'Lists all routes in your application';

    /**
     * The router instance populated with routes
     *
     * @var Router|null
     */
    private $router;

    /**
     * Constructor of the class
     *
     * @param Router|null $router
     */
    public function __construct(?Router $router = null)
    {
        parent::__construct();

        $this->setName(static::$defaultName);
        $this->setDescription(static::$defaultDescription);

        $this->router = $router;
    }

    /**
     * Gets the router instance populated with routes
     *
     * @return Router
     *
     * @throws RuntimeException
     *         If the command doesn't contain the router instance.
     *
     * @since 2.11.0
     */
    protected function getRouter() : Router
    {
        if (null === $this->router) {
            throw new RuntimeException(sprintf(
                'The %2$s() method MUST return the %1$s class instance. ' .
                'Pass the %1$s class instance to the constructor, or override the %2$s() method.',
                Router::class,
                __METHOD__
            ));
        }

        return $this->router;
    }

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
