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
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutioner;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutionerInterface;
use Sunrise\Http\Router\ParameterResolving\ParameterResolver\ParameterResolverInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutioner;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutionerInterface;
use Sunrise\Http\Router\ResponseResolving\ResponseResolver\ResponseResolverInterface;
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
     * @var ParameterResolutionerInterface|null
     */
    private ?ParameterResolutionerInterface $parameterResolutioner;

    /**
     * @var ResponseResolutionerInterface|null
     */
    private ?ResponseResolutionerInterface $responseResolutioner;

    /**
     * Constructor of the class
     *
     * @param RouteCollectionFactoryInterface|null $collectionFactory
     * @param RouteFactoryInterface|null $routeFactory
     * @param ReferenceResolverInterface|null $referenceResolver
     * @param ParameterResolutionerInterface|null $parameterResolutioner
     * @param ResponseResolutionerInterface|null $responseResolutioner
     */
    public function __construct(
        ?RouteCollectionFactoryInterface $collectionFactory = null,
        ?RouteFactoryInterface $routeFactory = null,
        ?ReferenceResolverInterface $referenceResolver = null,
        ?ParameterResolutionerInterface $parameterResolutioner = null,
        ?ResponseResolutionerInterface $responseResolutioner = null,
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();

        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;

        $this->referenceResolver = $referenceResolver ?? new ReferenceResolver(
            $this->parameterResolutioner ??= new ParameterResolutioner(),
            $this->responseResolutioner ??= new ResponseResolutioner(),
        );
    }

    /**
     * Adds the given parameter resolver(s) to the parameter resolutioner
     *
     * @param ParameterResolverInterface ...$resolvers
     *
     * @return void
     *
     * @throws LogicException
     *         If a custom reference resolver has been set,
     *         but a parameter resolutioner has not been set.
     *
     * @since 3.0.0
     */
    public function addParameterResolver(ParameterResolverInterface ...$resolvers): void
    {
        if (!isset($this->parameterResolutioner)) {
            throw new LogicException(
                'The config route loader cannot accept parameter resolvers ' .
                'because a custom reference resolver has been set, ' .
                'but a parameter resolutioner has not been set.'
            );
        }

        $this->parameterResolutioner->addResolver(...$resolvers);
    }

    /**
     * Adds the given response resolver(s) to the response resolutioner
     *
     * @param ResponseResolverInterface ...$resolvers
     *
     * @return void
     *
     * @throws LogicException
     *         If a custom reference resolver has been set,
     *         but a response resolutioner has not been set.
     *
     * @since 3.0.0
     */
    public function addResponseResolver(ResponseResolverInterface ...$resolvers): void
    {
        if (!isset($this->responseResolutioner)) {
            throw new LogicException(
                'The config route loader cannot accept response resolvers ' .
                'because a custom reference resolver has been set, ' .
                'but a response resolutioner has not been set.'
            );
        }

        $this->responseResolutioner->addResolver(...$resolvers);
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
