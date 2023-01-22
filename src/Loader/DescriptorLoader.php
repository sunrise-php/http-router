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
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader as AnnotationReaderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Annotation\Host;
use Sunrise\Http\Router\Annotation\Middleware;
use Sunrise\Http\Router\Annotation\Postfix;
use Sunrise\Http\Router\Annotation\Prefix;
use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\ResponseResolverInterface;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Reflector;
use SplFileInfo;

/**
 * Import functions
 */
use function array_diff;
use function class_exists;
use function get_debug_type;
use function get_declared_classes;
use function hash;
use function is_dir;
use function is_string;
use function usort;

/**
 * Import constants
 */
use const PHP_MAJOR_VERSION;

/**
 * DescriptorLoader
 */
class DescriptorLoader implements LoaderInterface
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
     * @var AnnotationReaderInterface|null
     */
    private ?AnnotationReaderInterface $annotationReader = null;

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

        if (PHP_MAJOR_VERSION === 7) {
            $this->useDefaultAnnotationReader();
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
     * Sets the given annotation reader to the loader
     *
     * @param AnnotationReaderInterface|null $annotationReader
     *
     * @return void
     *
     * @since 3.0.0
     */
    public function setAnnotationReader(?AnnotationReaderInterface $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
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
     * Uses the default annotation reader
     *
     * @return void
     *
     * @throws LogicException
     *         If the "doctrine/annotations" package isn't installed.
     *
     * @since 3.0.0
     */
    public function useDefaultAnnotationReader(): void
    {
        if (!class_exists(AnnotationReader::class)) {
            throw new LogicException(
                'Loading routes from descriptors requires an uninstalled "doctrine/annotations" package, ' .
                'run the command "composer install doctrine/annotations" and try again'
            );
        }

        $this->setAnnotationReader(new AnnotationReader());
    }

    /**
     * {@inheritdoc}
     */
    public function attach($resource): void
    {
        if (is_string($resource) && class_exists($resource)) {
            $this->resources[] = $resource;
            return;
        }

        if (is_string($resource) && is_dir($resource)) {
            $classNames = $this->scandir($resource);
            foreach ($classNames as $className) {
                $this->resources[] = $className;
            }

            return;
        }

        throw new InvalidLoaderResourceException(sprintf(
            'Descriptor route loader only handles class names or directory paths, ' .
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
        $descriptors = $this->getDescriptors();

        $routes = [];
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
     * and then tries to cache and return them
     *
     * @return list<Route>
     */
    private function getDescriptors(): array
    {
        $cacheKey = $this->getCacheKey();

        if (isset($this->cache) && $this->cache->has($cacheKey)) {
            /** @var list<Route> */
            return $this->cache->get($cacheKey);
        }

        $descriptors = $this->collectDescriptors();

        if (isset($this->cache)) {
            $this->cache->set($cacheKey, $descriptors);
        }

        return $descriptors;
    }

    /**
     * Collects and returns descriptors from the loader resources
     *
     * @return list<Route>
     */
    private function collectDescriptors(): array
    {
        $result = [];
        foreach ($this->resources as $resource) {
            $class = new ReflectionClass($resource);
            $descriptors = $this->getDescriptorsFromClass($class);
            foreach ($descriptors as $descriptor) {
                $result[] = $descriptor;
            }
        }

        usort($result, static function (Route $a, Route $b): int {
            return $b->priority <=> $a->priority;
        });

        return $result;
    }

    /**
     * Gets descriptors from the given class
     *
     * @param ReflectionClass $class
     *
     * @return list<Route>
     */
    private function getDescriptorsFromClass(ReflectionClass $class): array
    {
        // e.g., interfaces, traits, enums, abstract classes,
        // classes with private constructor...
        if (!$class->isInstantiable()) {
            return [];
        }

        $result = [];

        if ($class->isSubclassOf(RequestHandlerInterface::class)) {
            $annotations = $this->getAnnotationsFromClassOrMethod($class, Route::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0];
                $this->supplementDescriptorFromClassOrMethod($descriptor, $class);
                $descriptor->holder = $class->getName();
                $result[] = $descriptor;
            }
        }

        foreach ($class->getMethods() as $method) {
            // ignore non-public methods...
            if (!$method->isPublic()) {
                continue;
            }

            $annotations = $this->getAnnotationsFromClassOrMethod($method, Route::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0];
                $this->supplementDescriptorFromClassOrMethod($descriptor, $class);
                $this->supplementDescriptorFromClassOrMethod($descriptor, $method);
                $descriptor->holder = [$class->getName(), $method->getName()];
                $result[] = $descriptor;
            }
        }

        return $result;
    }

    /**
     * Supplements the given descriptor from the given class or method
     *
     * @param Route $descriptor
     * @param ReflectionClass|ReflectionMethod $reflector
     *
     * @return void
     */
    private function supplementDescriptorFromClassOrMethod(Route $descriptor, Reflector $reflector): void
    {
        $annotations = $this->getAnnotationsFromClassOrMethod($reflector, Host::class);
        if (isset($annotations[0])) {
            $descriptor->host = $annotations[0]->value;
        }

        $annotations = $this->getAnnotationsFromClassOrMethod($reflector, Prefix::class);
        if (isset($annotations[0])) {
            $descriptor->path = $annotations[0]->value . $descriptor->path;
        }

        $annotations = $this->getAnnotationsFromClassOrMethod($reflector, Postfix::class);
        if (isset($annotations[0])) {
            $descriptor->path = $descriptor->path . $annotations[0]->value;
        }

        $annotations = $this->getAnnotationsFromClassOrMethod($reflector, Middleware::class);
        foreach ($annotations as $annotation) {
            $descriptor->middlewares[] = $annotation->value;
        }
    }

    /**
     * Gets annotations from the given class or method
     *
     * @param ReflectionClass|ReflectionMethod $reflector
     * @param class-string<T> $annotationName
     *
     * @return list<T>
     *
     * @template T
     */
    private function getAnnotationsFromClassOrMethod(Reflector $reflector, string $annotationName): array
    {
        $result = [];

        if (PHP_MAJOR_VERSION === 8) {
            /** @var ReflectionAttribute[] */
            $attributes = $reflector->getAttributes($annotationName);
            foreach ($attributes as $attribute) {
                /** @var T */
                $result[] = $attribute->newInstance();
            }
        }

        if (isset($this->annotationReader)) {
            $annotations = ($reflector instanceof ReflectionClass) ?
                $this->annotationReader->getClassAnnotations($reflector) :
                $this->annotationReader->getMethodAnnotations($reflector);

            foreach ($annotations as $annotation) {
                if ($annotation instanceof $annotationName) {
                    $result[] = $annotation;
                }
            }
        }

        return $result;
    }

    /**
     * Scans the given directory and returns the found classes
     *
     * @param string $directory
     *
     * @return class-string[]
     */
    private function scandir(string $directory): array
    {
        /** @var array<string, class-string[]> */
        static $cache = [];

        if (isset($cache[$directory])) {
            return $cache[$directory];
        }

        $known = get_declared_classes();

        /** @var Iterator<SplFileInfo> */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($files as $file) {
            if ('php' === $file->getExtension()) {
                /** @psalm-suppress UnresolvableInclude */
                require_once $file->getPathname();
            }
        }

        $cache[$directory] = array_diff(get_declared_classes(), $known);

        return $cache[$directory];
    }
}
