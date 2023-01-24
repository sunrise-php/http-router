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
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Annotation\Host;
use Sunrise\Http\Router\Annotation\Middleware;
use Sunrise\Http\Router\Annotation\Postfix;
use Sunrise\Http\Router\Annotation\Prefix;
use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\AnnotationReader;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\ResponseResolverInterface;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;
use ReflectionClass;
use ReflectionMethod;
use Reflector;

/**
 * Import functions
 */
use function class_exists;
use function hash;
use function is_dir;
use function is_string;
use function usort;
use function Sunrise\Http\Router\get_dir_classes;

/**
 * Import constants
 */
use const PHP_MAJOR_VERSION;

/**
 * DescriptorLoader
 */
final class DescriptorLoader implements LoaderInterface
{

    /**
     * @var list<class-string>
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
     * @var AnnotationReader
     */
    private AnnotationReader $annotationReader;

    /**
     * @var CacheInterface|null
     */
    private ?CacheInterface $cache = null;

    /**
     * @var string|null
     */
    private ?string $cacheKey = null;

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

        $this->annotationReader = new AnnotationReader();

        if (8 > PHP_MAJOR_VERSION) {
            $this->annotationReader->useDefaultAnnotationReader();
        }
    }

    /**
     * Sets the given container to the reference resolver
     *
     * @param ContainerInterface|null $container
     *
     * @return void
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
     * Sets the given annotation reader to the descriptor loader
     *
     * @param \Doctrine\Common\Annotations\Reader|null $annotationReader
     *
     * @return void
     *
     * @since 3.0.0
     */
    public function setAnnotationReader(?\Doctrine\Common\Annotations\Reader $annotationReader): void
    {
        $this->annotationReader->setAnnotationReader($annotationReader);
    }

    /**
     * Uses the default annotation reader
     *
     * @return void
     *
     * @since 3.0.0
     */
    public function useDefaultAnnotationReader(): void
    {
        $this->annotationReader->useDefaultAnnotationReader();
    }

    /**
     * Gets the loader cache
     *
     * @return CacheInterface|null
     */
    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }

    /**
     * Sets the given cache to the loader
     *
     * @param CacheInterface|null $cache
     *
     * @return void
     */
    public function setCache(?CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * Gets the loader cache key
     *
     * @return string
     *
     * @since 2.10.0
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey ??= hash('md5', 'router:descriptors');
    }

    /**
     * Sets the given cache key to the loader
     *
     * @param string|null $cacheKey
     *
     * @return void
     *
     * @since 2.10.0
     */
    public function setCacheKey(?string $cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * {@inheritdoc}
     */
    public function attach($resource): void
    {
        if (!is_string($resource)) {
            throw new InvalidArgumentException(
                'The descriptor route loader only handles string resources'
            );
        }

        if (is_dir($resource)) {
            $classnames = get_dir_classes($resource);
            foreach ($classnames as $classname) {
                $this->resources[] = $classname;
            }

            return;
        }

        if (class_exists($resource)) {
            $this->resources[] = $resource;
            return;
        }

        throw new InvalidArgumentException(sprintf(
            'The descriptor route loader only handles class names or directory paths, ' .
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
        $routes = [];
        $descriptors = $this->getDescriptors();
        foreach ($descriptors as $descriptor) {
            $routes[] = $this->routeFactory->createRoute(
                $descriptor->name,
                $descriptor->path,
                $descriptor->methods,
                $this->referenceResolver->resolveRequestHandler($descriptor->holder),
                $this->referenceResolver->resolveMiddlewares($descriptor->middlewares),
                $descriptor->attributes
            )
            ->setHost($descriptor->host)
            ->setSummary($descriptor->summary)
            ->setDescription($descriptor->description)
            ->setTags(...$descriptor->tags);
        }

        return $this->collectionFactory->createCollection(...$routes);
    }

    /**
     * Gets descriptors from the cache if they are stored in it,
     * otherwise collects them from the loader resources,
     * then tries to cache and return them
     *
     * @return list<Route>
     */
    private function getDescriptors(): array
    {
        $key = $this->getCacheKey();

        if (isset($this->cache) && $this->cache->has($key)) {
            /** @var list<Route> */
            return $this->cache->get($key);
        }

        $result = [];

        foreach ($this->resources as $resource) {
            $descriptors = $this->getClassDescriptors(
                new ReflectionClass($resource)
            );

            foreach ($descriptors as $descriptor) {
                $result[] = $descriptor;
            }
        }

        usort($result, static function (Route $a, Route $b): int {
            return $b->priority <=> $a->priority;
        });

        if (isset($this->cache)) {
            $this->cache->set($key, $result);
        }

        return $result;
    }

    /**
     * Gets descriptors from the given class
     *
     * @param ReflectionClass $class
     *
     * @return list<Route>
     */
    private function getClassDescriptors(ReflectionClass $class): array
    {
        // e.g., interfaces, traits, enums, abstract classes,
        // classes with private constructor...
        if (!$class->isInstantiable()) {
            return [];
        }

        $result = [];

        if ($class->isSubclassOf(RequestHandlerInterface::class)) {
            $annotations = $this->annotationReader->getClassAnnotations($class, Route::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0];
                $descriptor->holder = $class->getName();
                $this->supplementDescriptor($descriptor, $class);
                $result[] = $descriptor;
            }
        }

        foreach ($class->getMethods() as $method) {
            // ignore non-public methods...
            if (!$method->isPublic()) {
                continue;
            }

            $annotations = $this->annotationReader->getMethodAnnotations($method, Route::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0];
                $descriptor->holder = [$class->getName(), $method->getName()];
                $this->supplementDescriptor($descriptor, $class);
                $this->supplementDescriptor($descriptor, $method);
                $result[] = $descriptor;
            }
        }

        return $result;
    }

    /**
     * Supplements the given descriptor from the given class or method
     *
     * @param Route $descriptor
     * @param ReflectionClass|ReflectionMethod $classOrMethod
     *
     * @return void
     */
    private function supplementDescriptor(Route $descriptor, Reflector $classOrMethod): void
    {
        $annotations = $this->annotationReader->getClassOrMethodAnnotations($classOrMethod, Host::class);
        if (isset($annotations[0])) {
            $descriptor->host = $annotations[0]->value;
        }

        $annotations = $this->annotationReader->getClassOrMethodAnnotations($classOrMethod, Prefix::class);
        if (isset($annotations[0])) {
            $descriptor->path = $annotations[0]->value . $descriptor->path;
        }

        $annotations = $this->annotationReader->getClassOrMethodAnnotations($classOrMethod, Postfix::class);
        if (isset($annotations[0])) {
            $descriptor->path = $descriptor->path . $annotations[0]->value;
        }

        $annotations = $this->annotationReader->getClassOrMethodAnnotations($classOrMethod, Middleware::class);
        foreach ($annotations as $annotation) {
            $descriptor->middlewares[] = $annotation->value;
        }
    }
}
