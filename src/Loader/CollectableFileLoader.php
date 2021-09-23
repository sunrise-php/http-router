<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Loader;

/**
 * Import classes
 */
use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;

/**
 * Import functions
 */
use function glob;
use function is_dir;
use function is_file;
use function sprintf;

/**
 * CollectableFileLoader
 */
class CollectableFileLoader implements LoaderInterface
{

    /**
     * @var string[]
     */
    private $resources = [];

    /**
     * @var RouteCollectionFactoryInterface
     */
    private $collectionFactory;

    /**
     * @var RouteFactoryInterface
     */
    private $routeFactory;

    /**
     * @var null|ContainerInterface
     */
    private $container = null;

    /**
     * Constructor of the class
     *
     * @param null|RouteCollectionFactoryInterface $collectionFactory
     * @param null|RouteFactoryInterface $routeFactory
     */
    public function __construct(
        ?RouteCollectionFactoryInterface $collectionFactory = null,
        ?RouteFactoryInterface $routeFactory = null
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();
    }

    /**
     * Gets the loader container
     *
     * @return null|ContainerInterface
     *
     * @since 2.9.0
     */
    public function getContainer() : ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Sets the given container to the loader
     *
     * @param null|ContainerInterface $container
     *
     * @return void
     *
     * @since 2.9.0
     */
    public function setContainer(?ContainerInterface $container) : void
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function attach($resource) : void
    {
        if (is_dir($resource)) {
            $resources = glob($resource . '/*.php');
            foreach ($resources as $resource) {
                $this->resources[] = $resource;
            }

            return;
        }

        if (!is_file($resource)) {
            throw new InvalidLoaderResourceException(
                sprintf('The resource "%s" is not found.', $resource)
            );
        }

        $this->resources[] = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function attachArray(array $resources) : void
    {
        foreach ($resources as $resource) {
            $this->attach($resource);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load() : RouteCollectionInterface
    {
        $collect = new RouteCollector(
            $this->collectionFactory,
            $this->routeFactory
        );

        $collect->setContainer($this->container);

        foreach ($this->resources as $resource) {
            (function () use ($resource) {
                require $resource;
            })->call($collect);
        }

        return $collect->getCollection();
    }
}
