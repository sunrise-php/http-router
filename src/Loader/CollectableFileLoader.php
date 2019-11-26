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
use Sunrise\Http\Router\Exception\InvalidLoadResourceException;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;

/**
 * Import functions
 */
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
     * @param null|RouteCollectionFactoryInterface $collectionFactory
     * @param null|RouteFactoryInterface $routeFactory
     */
    public function __construct(
        RouteCollectionFactoryInterface $collectionFactory = null,
        RouteFactoryInterface $routeFactory = null
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function attach($resource) : void
    {
        if (!is_file($resource)) {
            throw new InvalidLoadResourceException(
                sprintf('The "%s" resource not found.', $resource)
            );
        }

        $this->resources[] = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function load() : RouteCollectionInterface
    {
        $collect = new RouteCollector(
            $this->collectionFactory,
            $this->routeFactory
        );

        foreach ($this->resources as $resource) {
            (function () use ($resource) {
                require $resource;
            })->call($collect);
        }

        return $collect->getCollection();
    }
}
