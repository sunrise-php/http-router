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
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
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
 * ConfigLoader
 */
class ConfigLoader implements LoaderInterface
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
     * @var ReferenceResolverInterface
     */
    private $referenceResolver;

    /**
     * Constructor of the class
     *
     * @param RouteCollectionFactoryInterface|null $collectionFactory
     * @param RouteFactoryInterface|null $routeFactory
     * @param ReferenceResolverInterface|null $referenceResolver
     */
    public function __construct(
        ?RouteCollectionFactoryInterface $collectionFactory = null,
        ?RouteFactoryInterface $routeFactory = null,
        ?ReferenceResolverInterface $referenceResolver = null
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();
        $this->referenceResolver = $referenceResolver ?? new ReferenceResolver();
    }

    /**
     * Gets the loader container
     *
     * @return ContainerInterface|null
     *
     * @since 2.9.0
     */
    public function getContainer() : ?ContainerInterface
    {
        return $this->referenceResolver->getContainer();
    }

    /**
     * Sets the given container to the loader
     *
     * @param ContainerInterface|null $container
     *
     * @return void
     *
     * @since 2.9.0
     */
    public function setContainer(?ContainerInterface $container) : void
    {
        $this->referenceResolver->setContainer($container);
    }

    /**
     * {@inheritdoc}
     */
    public function attach($resource) : void
    {
        if (is_dir($resource)) {
            $fileNames = glob($resource . '/*.php');
            foreach ($fileNames as $fileName) {
                $this->resources[] = $fileName;
            }

            return;
        }

        if (!is_file($resource)) {
            throw new InvalidLoaderResourceException(sprintf(
                'The resource "%s" is not found.',
                $resource
            ));
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
        $collector = new RouteCollector(
            $this->collectionFactory,
            $this->routeFactory,
            $this->referenceResolver
        );

        foreach ($this->resources as $resource) {
            (function () use ($resource) {
                /**
                 * @psalm-suppress UnresolvableInclude
                 */
                require $resource;
            })->call($collector);
        }

        return $collector->getCollection();
    }
}
