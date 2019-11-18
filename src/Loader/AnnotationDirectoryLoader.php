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
use Sunrise\Http\Router\Route as BaseRoute;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;

/**
 * Import functions
 */
use function array_diff;
use function get_declared_classes;
use function iterator_to_array;
use function usort;

/**
 * AnnotationDirectoryLoader
 */
class AnnotationDirectoryLoader implements LoaderInterface
{

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
     */
    public function __construct()
    {
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
    public function load($destination) : array
    {
        $annotations = $this->findAnnotations($destination);

        $routes = [];
        foreach ($annotations as $annotation) {
            $routes[] = new BaseRoute(
                $annotation->name,
                $annotation->path,
                $annotation->methods,
                $this->initClass($annotation->source),
                $this->initClasses(...$annotation->middlewares),
                $annotation->attributes
            );
        }

        return $routes;
    }

    /**
     * Finds annotations in the given destination
     *
     * @param string $destination
     *
     * @return object[]
     */
    private function findAnnotations(string $destination) : array
    {
        $classes = $this->findClasses($destination);

        $annotations = [];
        foreach ($classes as $class) {
            $annotation = $this->annotationReader->getClassAnnotation(
                new ReflectionClass($class),
                AnnotationRoute::class
            );

            if ($annotation) {
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
     * Finds classes in the given destination
     *
     * @param string $destination
     *
     * @return string[]
     */
    private function findClasses(string $destination) : array
    {
        $files = $this->findFiles($destination);
        $declared = get_declared_classes();

        foreach ($files as $file) {
            require_once $file->getRealPath();
        }

        return array_diff(get_declared_classes(), $declared);
    }

    /**
     * Finds files in the given destination
     *
     * @param string $destination
     *
     * @return string[]
     */
    private function findFiles(string $destination) : array
    {
        $directory = new RecursiveDirectoryIterator($destination);
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
