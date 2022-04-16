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
use Doctrine\Common\Annotations\SimpleAnnotationReader;
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
use Sunrise\Http\Router\Exception\UnresolvableReferenceException;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use Reflector;

/**
 * Import functions
 */
use function array_diff;
use function class_exists;
use function get_declared_classes;
use function hash;
use function is_dir;
use function sprintf;
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
     * @var class-string[]
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
     * @var SimpleAnnotationReader|null
     */
    private $annotationReader = null;

    /**
     * @var CacheInterface|null
     */
    private $cache = null;

    /**
     * @var string|null
     */
    private $cacheKey = null;

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

        // the "doctrine/annotations" package must be installed manually
        if (class_exists(SimpleAnnotationReader::class)) {
            $this->annotationReader = /** @scrutinizer ignore-deprecated */ new SimpleAnnotationReader();
            $this->annotationReader->addNamespace('Sunrise\Http\Router\Annotation');
        }
    }

    /**
     * Gets the loader container
     *
     * @return ContainerInterface|null
     */
    public function getContainer() : ?ContainerInterface
    {
        return $this->referenceResolver->getContainer();
    }

    /**
     * Gets the loader cache
     *
     * @return CacheInterface|null
     */
    public function getCache() : ?CacheInterface
    {
        return $this->cache;
    }

    /**
     * Gets the loader cache key
     *
     * @return string|null
     *
     * @since 2.10.0
     */
    public function getCacheKey() : ?string
    {
        return $this->cacheKey;
    }

    /**
     * Sets the given container to the loader
     *
     * @param ContainerInterface|null $container
     *
     * @return void
     */
    public function setContainer(?ContainerInterface $container) : void
    {
        $this->referenceResolver->setContainer($container);
    }

    /**
     * Sets the given cache to the loader
     *
     * @param CacheInterface|null $cache
     *
     * @return void
     */
    public function setCache(?CacheInterface $cache) : void
    {
        $this->cache = $cache;
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
    public function setCacheKey(?string $cacheKey) : void
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * {@inheritdoc}
     */
    public function attach($resource) : void
    {
        if (is_dir($resource)) {
            $classNames = $this->scandir($resource);
            foreach ($classNames as $className) {
                $this->resources[] = $className;
            }

            return;
        }

        if (!class_exists($resource)) {
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
     *
     * @throws UnresolvableReferenceException
     *         If one of the found middlewares cannot be resolved.
     */
    public function load() : RouteCollectionInterface
    {
        $descriptors = $this->getCachedDescriptors();

        $routes = [];
        foreach ($descriptors as $descriptor) {
            $middlewares = [];
            foreach ($descriptor->middlewares as $className) {
                $middlewares[] = $this->referenceResolver->toMiddleware($className);
            }

            $routes[] = $this->routeFactory->createRoute(
                $descriptor->name,
                $descriptor->path,
                $descriptor->methods,
                $this->referenceResolver->toRequestHandler($descriptor->holder),
                $middlewares,
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
     * and then tries to cache them
     *
     * @return Route[]
     */
    private function getCachedDescriptors() : array
    {
        $key = $this->cacheKey ?? hash('md5', 'router:descriptors');

        if ($this->cache && $this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $result = $this->collectDescriptors();

        if ($this->cache) {
            $this->cache->set($key, $result);
        }

        return $result;
    }

    /**
     * Collects descriptors from the loader resources
     *
     * @return Route[]
     */
    private function collectDescriptors() : array
    {
        $result = [];
        foreach ($this->resources as $resource) {
            $class = new ReflectionClass($resource);
            $descriptors = $this->getClassDescriptors($class);
            foreach ($descriptors as $descriptor) {
                $result[] = $descriptor;
            }
        }

        usort($result, function (Route $a, Route $b) : int {
            return $b->priority <=> $a->priority;
        });

        return $result;
    }

    /**
     * Gets descriptors from the given class
     *
     * @param ReflectionClass $class
     *
     * @return Route[]
     */
    private function getClassDescriptors(ReflectionClass $class) : array
    {
        if ($class->isAbstract()) {
            return [];
        }

        $result = [];

        if ($class->isSubclassOf(RequestHandlerInterface::class)) {
            $annotations = $this->getAnnotations($class, Route::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0];
                $this->supplementDescriptor($descriptor, $class);
                $descriptor->holder = $class->getName();
                $result[] = $descriptor;
            }
        }

        foreach ($class->getMethods() as $method) {
            // ignore non-available methods...
            if ($method->isStatic() ||
                $method->isPrivate() ||
                $method->isProtected()) {
                continue;
            }

            $annotations = $this->getAnnotations($method, Route::class);
            if (isset($annotations[0])) {
                $descriptor = $annotations[0];
                $this->supplementDescriptor($descriptor, $class);
                $this->supplementDescriptor($descriptor, $method);
                $descriptor->holder = [$class->getName(), $method->getName()];
                $result[] = $descriptor;
            }
        }

        return $result;
    }

    /**
     * Supplements the given descriptor from the given class or method with data such as:
     * host, path prefix, path postfix and middlewares
     *
     * ```php
     * #[Prefix('/api/v1')]
     * class SomeController {
     *
     *   #[Route('foo', path: '/foo')]
     *   public function foo() {
     *     // will be available at: /api/v1/foo
     *   }
     *
     *   #[Route('bar', path: '/bar')]
     *   public function bar() {
     *     // will be available at: /api/v1/bar
     *   }
     * }
     * ```
     *
     * @param Route $descriptor
     * @param ReflectionClass|ReflectionMethod $reflector
     *
     * @return void
     */
    private function supplementDescriptor(Route $descriptor, Reflector $reflector) : void
    {
        $annotations = $this->getAnnotations($reflector, Host::class);
        if (isset($annotations[0])) {
            $descriptor->host = $annotations[0]->value;
        }

        $annotations = $this->getAnnotations($reflector, Prefix::class);
        if (isset($annotations[0])) {
            $descriptor->path = $annotations[0]->value . $descriptor->path;
        }

        $annotations = $this->getAnnotations($reflector, Postfix::class);
        if (isset($annotations[0])) {
            $descriptor->path = $descriptor->path . $annotations[0]->value;
        }

        $annotations = $this->getAnnotations($reflector, Middleware::class);
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
     * @return T[]
     *
     * @template T
     */
    private function getAnnotations(Reflector $reflector, string $annotationName) : array
    {
        $result = [];

        if (8 === PHP_MAJOR_VERSION) {
            $attributes = $reflector->getAttributes($annotationName);
            foreach ($attributes as $attribute) {
                $result[] = $attribute->newInstance();
            }
        }

        if (empty($result) and isset($this->annotationReader)) {
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
    private function scandir(string $directory) : array
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        $declared = get_declared_classes();

        foreach ($files as $file) {
            if ('php' === $file->getExtension()) {
                /**
                 * @psalm-suppress UnresolvableInclude
                 */
                require_once $file->getPathname();
            }
        }

        return array_diff(get_declared_classes(), $declared);
    }
}
