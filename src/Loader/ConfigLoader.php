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
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolver\DependencyInjectionParameterResolver;
use Sunrise\Http\Router\ParameterResolutioner;
use Sunrise\Http\Router\ParameterResolutionerInterface;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\ResponseResolutioner;
use Sunrise\Http\Router\ResponseResolutionerInterface;
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
     * @var ParameterResolutionerInterface|null
     */
    private ?ParameterResolutionerInterface $parameterResolutioner = null;

    /**
     * @var ResponseResolutionerInterface|null
     */
    private ?ResponseResolutionerInterface $responseResolutioner = null;

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
        ?ResponseResolutionerInterface $responseResolutioner = null
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();

        $this->parameterResolutioner = $parameterResolutioner;
        $this->responseResolutioner = $responseResolutioner;

        $this->referenceResolver = $referenceResolver ?? new ReferenceResolver(
            $this->parameterResolutioner ??= new ParameterResolutioner(),
            $this->responseResolutioner ??= new ResponseResolutioner()
        );
    }

    /**
     * Sets the given container to the parameter resolutioner
     *
     * @param ContainerInterface $container
     *
     * @return void
     *
     * @throws LogicException
     *         If a custom reference resolver was setted
     *         and a parameter resolutioner was not passed.
     */
    public function setContainer(ContainerInterface $container): void
    {
        if (!isset($this->parameterResolutioner)) {
            throw new LogicException(
                'The config route loader cannot accept the container ' .
                'because a custom reference resolver was setted ' .
                'and a parameter resolutioner was not passed'
            );
        }

        $this->parameterResolutioner->addResolver(
            new DependencyInjectionParameterResolver($container)
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
     *         If a custom reference resolver was setted
     *         and a parameter resolutioner was not passed.
     *
     * @since 3.0.0
     */
    public function addParameterResolver(ParameterResolverInterface ...$resolvers): void
    {
        if (!isset($this->parameterResolutioner)) {
            throw new LogicException(
                'The config route loader cannot accept the parameter resolver ' .
                'because a custom reference resolver was setted' .
                'and a parameter resolutioner was not passed'
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
     *         If a custom reference resolver was setted
     *         and a response resolutioner was not passed.
     *
     * @since 3.0.0
     */
    public function addResponseResolver(ResponseResolverInterface ...$resolvers): void
    {
        if (!isset($this->responseResolutioner)) {
            throw new LogicException(
                'The config route loader cannot accept the response resolver ' .
                'because a custom reference resolver was setted' .
                'and a response resolutioner was not passed'
            );
        }

        $this->responseResolutioner->addResolver(...$resolvers);
    }

    /**
     * {@inheritdoc}
     */
    public function attach($resource): void
    {
        if (!is_string($resource)) {
            throw new InvalidArgumentException(
                'The config route loader only handles string resources'
            );
        }

        if (is_dir($resource)) {
            $filenames = glob($resource . '/*.php');
            foreach ($filenames as $filename) {
                $this->resources[] = $filename;
            }

            return;
        }

        if (is_file($resource)) {
            $this->resources[] = $resource;
            return;
        }

        throw new InvalidArgumentException(sprintf(
            'The config route loader only handles file or directory paths, ' .
            'however the given resource "%s" is not one of them',
            $resource
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
