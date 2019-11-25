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
use Sunrise\Http\Router\Annotation\Route as AnnotationRoute;
use Sunrise\Http\Router\Exception\InvalidLoadResourceException;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\RouteCollectionInterface;
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
use function is_dir;
use function iterator_to_array;
use function sprintf;
use function usort;

/**
 * AnnotationDirectoryLoader
 */
class AnnotationDirectoryLoader implements LoaderInterface
{

    /**
     * @var string[]
     */
    private $resources = [];

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
     * Constructor of the class
     *
     * @param null|RouteFactoryInterface $routeFactory
     */
    public function __construct(RouteFactoryInterface $routeFactory = null)
    {
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
     * {@inheritDoc}
     */
    public function attach($resource) : void
    {
        if (!is_dir($resource)) {
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
        $routes = [];
        foreach ($this->resources as $resource) {
            $annotations = $this->findAnnotations($resource);
            foreach ($annotations as $annotation) {
                $routes[] = $this->routeFactory->createRoute(
                    $annotation->name,
                    $annotation->path,
                    $annotation->methods,
                    $this->initClass($annotation->source),
                    $this->initClasses(...$annotation->middlewares),
                    $annotation->attributes
                );
            }
        }

        return new RouteCollection(...$routes);
    }

    /**
     * Finds annotations in the given resource
     *
     * @param string $resource
     *
     * @return AnnotationRoute[]
     */
    private function findAnnotations(string $resource) : array
    {
        $classes = $this->findClasses($resource);

        $annotations = [];
        foreach ($classes as $class) {
            $annotation = $this->annotationReader->getClassAnnotation(
                new ReflectionClass($class),
                AnnotationRoute::class
            );

            if ($annotation) {
                AnnotationRoute::assertValidSource($class);

                $annotation->source = $class;
                $annotations[] = $annotation;
            }
        }

        usort($annotations, function ($a, $b) {
            return $b->priority <=> $a->priority;
        });

        return $annotations;
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
