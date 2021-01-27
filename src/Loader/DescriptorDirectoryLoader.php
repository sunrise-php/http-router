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
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Annotation\Route as AnnotationRouteDescriptor;
use Sunrise\Http\Router\Attribute\Route as AttributeRouteDescriptor;
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteDescriptorInterface;
use Sunrise\Http\Router\RouteFactory;
use Sunrise\Http\Router\RouteFactoryInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;

/**
 * Import functions
 */
use function array_diff;
use function get_declared_classes;
use function hash;
use function is_dir;
use function is_subclass_of;
use function iterator_to_array;
use function sprintf;
use function uasort;

/**
 * Import constants
 */
use const PHP_MAJOR_VERSION;

/**
 * DescriptorDirectoryLoader
 *
 * @since 2.6.0
 */
class DescriptorDirectoryLoader implements LoaderInterface
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
     * @var SimpleAnnotationReader
     */
    private $annotationReader;

    /**
     * @var null|ContainerInterface
     */
    private $container;

    /**
     * @var null|CacheInterface
     */
    private $cache;

    /**
     * Constructor of the class
     *
     * @param null|RouteCollectionFactoryInterface $collectionFactory
     * @param null|RouteFactoryInterface $routeFactory
     */
    public function __construct(
        RouteCollectionFactoryInterface $collectionFactory = null,
        RouteFactoryInterface $routeFactory = null
    ) {
        $this->collectionFactory = $collectionFactory ?? new RouteCollectionFactory();
        $this->routeFactory = $routeFactory ?? new RouteFactory();

        $this->annotationReader = new SimpleAnnotationReader();
        $this->annotationReader->addNamespace('Sunrise\Http\Router\Annotation');
    }

    /**
     * Gets the loader container
     *
     * @return null|ContainerInterface
     */
    public function getContainer() : ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Gets the loader cache
     *
     * @return null|CacheInterface
     */
    public function getCache() : ?CacheInterface
    {
        return $this->cache;
    }

    /**
     * Sets the given container to the loader
     *
     * @param ContainerInterface $container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
    }

    /**
     * Sets the given cache to the loader
     *
     * @param CacheInterface $cache
     *
     * @return void
     */
    public function setCache(CacheInterface $cache) : void
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function attach($resource) : void
    {
        if (!is_dir($resource)) {
            throw new InvalidLoaderResourceException(
                sprintf('The resource "%s" is not found.', $resource)
            );
        }

        $this->resources[] = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function attachArray(array $resources) : void
    {
        foreach ($resources as $resource) {
            $this->attach($resource);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function load() : RouteCollectionInterface
    {
        $descriptors = [];
        foreach ($this->resources as $resource) {
            $descriptors += $this->fetchDescriptors($resource);
        }

        $routes = [];
        foreach ($descriptors as $class => $descriptor) {
            $routes[] = $this->routeFactory->createRoute(
                $descriptor->getName(),
                $descriptor->getPath(),
                $descriptor->getMethods(),
                $this->initClass($class),
                $this->initClasses(...$descriptor->getMiddlewares()),
                $descriptor->getAttributes()
            )
            ->setHost($descriptor->getHost())
            ->setSummary($descriptor->getSummary())
            ->setDescription($descriptor->getDescription())
            ->setTags(...$descriptor->getTags());
        }

        return $this->collectionFactory->createCollection(...$routes);
    }

    /**
     * Fetches descriptors for the given resource
     *
     * @param string $resource
     *
     * @return RouteDescriptorInterface[]
     *
     * @throws \Psr\SimpleCache\CacheException Depends on implementation PSR-16.
     */
    private function fetchDescriptors(string $resource) : array
    {
        if (!$this->cache) {
            return $this->findDescriptors($resource);
        }

        // some cache stores may have character restrictions for a key...
        $key = hash('md5', $resource);

        if (!$this->cache->has($key)) {
            $value = $this->findDescriptors($resource);

            // TTL should be set at the storage...
            $this->cache->set($key, $value);
        }

        return $this->cache->get($key);
    }

    /**
     * Finds descriptors in the given resource
     *
     * @param string $resource
     *
     * @return RouteDescriptorInterface[]
     */
    private function findDescriptors(string $resource) : array
    {
        $classes = $this->findClasses($resource);

        $descriptors = [];
        foreach ($classes as $class) {
            if (!is_subclass_of($class, RequestHandlerInterface::class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if (8 === PHP_MAJOR_VERSION) {
                $attribute = $reflection->getAttributes(AttributeRouteDescriptor::class)[0] ?? null;
                if (isset($attribute)) {
                    $descriptors[$class] = $attribute->newInstance();
                    continue;
                }
            }

            $annotation = $this->annotationReader->getClassAnnotation($reflection, AnnotationRouteDescriptor::class);
            if (isset($annotation)) {
                $descriptors[$class] = $annotation;
                continue;
            }
        }

        uasort($descriptors, function ($a, $b) {
            return $b->getPriority() <=> $a->getPriority();
        });

        return $descriptors;
    }

    /**
     * Finds classes in the given resource
     *
     * @param string $resource
     *
     * @return string[]
     */
    private function findClasses(string $resource) : array
    {
        $files = $this->findFiles($resource);
        $declared = get_declared_classes();

        foreach ($files as $file) {
            require_once $file;
        }

        return array_diff(get_declared_classes(), $declared);
    }

    /**
     * Finds files in the given resource
     *
     * @param string $resource
     *
     * @return string[]
     */
    private function findFiles(string $resource) : array
    {
        $flags = FilesystemIterator::CURRENT_AS_PATHNAME;

        $directory = new RecursiveDirectoryIterator($resource, $flags);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, '/\.php$/');

        return iterator_to_array($files);
    }

    /**
     * Initializes the given class
     *
     * @param string $class
     *
     * @return object
     */
    private function initClass(string $class)
    {
        if ($this->container && $this->container->has($class)) {
            return $this->container->get($class);
        }

        return new $class;
    }

    /**
     * Initializes the given classes
     *
     * @param string ...$classes
     *
     * @return object[]
     */
    private function initClasses(string ...$classes) : array
    {
        foreach ($classes as &$class) {
            $class = $this->initClass($class);
        }

        return $classes;
    }
}
