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

namespace Sunrise\Http\Router\Loader;

use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteCollector;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;

use function glob;
use function is_dir;
use function is_file;
use function is_string;
use function sprintf;

/**
 * ConfigLoader
 */
final class ConfigLoader implements LoaderInterface
{

    /**
     * List of files
     *
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
        ?ReferenceResolverInterface $referenceResolver = null,
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();
        $this->referenceResolver = $referenceResolver ?? new ReferenceResolver();
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException If the resource isn't valid.
     */
    public function attach(mixed $resource): void
    {
        if (!is_string($resource)) {
            throw new InvalidArgumentException(
                'The config route loader only handles string resources.'
            );
        }

        if (is_file($resource)) {
            $this->resources[] = $resource;
            return;
        }

        if (is_dir($resource)) {
            /** @var list<string> $filenames */
            $filenames = glob($resource . '/*.php');
            foreach ($filenames as $filename) {
                $this->resources[] = $filename;
            }

            return;
        }

        throw new InvalidArgumentException(sprintf(
            'The config route loader only handles file or directory paths, ' .
            'however the given resource "%s" is not one of them.',
            $resource,
        ));
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException If one of the given resources isn't valid.
     */
    public function attachArray(array $resources): void
    {
        /** @psalm-suppress MixedAssignment */
        foreach ($resources as $resource) {
            $this->attach($resource);
        }
    }

    /**
     * @inheritDoc
     */
    public function load(): RouteCollectionInterface
    {
        $collector = new RouteCollector(
            $this->collectionFactory,
            $this->routeFactory,
            $this->referenceResolver,
        );

        foreach ($this->resources as $resource) {
            (function (string $filename): void {
                /** @psalm-suppress UnresolvableInclude */
                require $filename;
            })->call($collector, $resource);
        }

        return $collector->getCollection();
    }
}
