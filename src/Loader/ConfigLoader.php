<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Loader;

/**
 * Import classes
 */
use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\ResponseResolverInterface;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;

/**
 * Import functions
 */
use function get_debug_type;
use function glob;
use function is_dir;
use function is_file;
use function is_string;

/**
 * ConfigLoader
 */
final class ConfigLoader implements LoaderInterface
{

    /**
     * @var list<string>
     */
    private array $resources = [];

    /**
     * @var RouteCollectionFactoryInterface
     */
    private RouteCollectionFactoryInterface $collectionFactory;

    /**
     * @var RouteFactoryInterface
     */
    private RouteFactoryInterface $routeFactory;

    /**
     * @var ReferenceResolverInterface
     */
    private ReferenceResolverInterface $referenceResolver;

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
     * Sets the given container to the reference resolver
     *
     * @param ContainerInterface|null $container
     *
     * @return void
     *
     * @since 2.9.0
     */
    public function setContainer(?ContainerInterface $container): void
    {
        $this->referenceResolver->setContainer($container);
    }

    /**
     * Adds the given parameter resolver(s) to the reference resolver
     *
     * @param ParameterResolverInterface ...$resolvers
     *
     * @return void
     *
     * @since 3.0.0
     */
    public function addParameterResolver(ParameterResolverInterface ...$resolvers): void
    {
        $this->referenceResolver->addParameterResolver(...$resolvers);
    }

    /**
     * Adds the given response resolver(s) to the reference resolver
     *
     * @param ResponseResolverInterface ...$resolvers
     *
     * @return void
     *
     * @since 3.0.0
     */
    public function addResponseResolver(ResponseResolverInterface ...$resolvers): void
    {
        $this->referenceResolver->addResponseResolver(...$resolvers);
    }

    /**
     * {@inheritdoc}
     */
    public function attach($resource): void
    {
        if (is_string($resource) && is_dir($resource)) {
            $filenames = glob($resource . '/*.php');
            foreach ($filenames as $filename) {
                $this->resources[] = $filename;
            }

            return;
        }

        if (is_string($resource) && is_file($resource)) {
            $this->resources[] = $resource;
            return;
        }

        throw new InvalidLoaderResourceException(sprintf(
            'Config route loader only handles file or directory paths, ' .
            'however the given resource "%s" is not one of them',
            is_string($resource) ? $resource : get_debug_type($resource)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function attachArray(array $resources): void
    {
        /** @psalm-suppress MixedAssignment */
        foreach ($resources as $resource) {
            $this->attach($resource);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(): RouteCollectionInterface
    {
        $collector = new RouteCollector(
            $this->collectionFactory,
            $this->routeFactory,
            $this->referenceResolver
        );

        foreach ($this->resources as $filename) {
            (function (string $filename): void {
                /** @psalm-suppress UnresolvableInclude */
                require $filename;
            })->call($collector, $filename);
        }

        return $collector->getCollection();
    }
}
