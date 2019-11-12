<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Annotation;

/**
 * Import classes
 */
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Sunrise\Http\Router\Route as BaseRoute;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;
use SplPriorityQueue;

/**
 * Import functions
 */
use function array_diff;
use function get_declared_classes;
use function iterator_to_array;

/**
 * AnnotationRouteLoader
 */
class AnnotationRouteLoader
{

    /**
     * @var SimpleAnnotationReader
     */
    private $annotationReader;

    /**
     * @var SplPriorityQueue
     */
    private $discoveredAnnotations;

    /**
     * Constructor of the class
     */
    public function __construct()
    {
        $this->annotationReader = new SimpleAnnotationReader();
        $this->annotationReader->addNamespace(__NAMESPACE__);

        $this->discoveredAnnotations = new SplPriorityQueue();
    }

    /**
     * Builds discovered routes
     *
     * @param null|callable $objectCreator
     *
     * @return array
     */
    public function buildRoutes(callable $objectCreator = null) : array
    {
        $routes = [];
        foreach ($this->discoveredAnnotations as $annotation) {
            $requestHandlerClass = $annotation->source->getName();
            $requestHandler = $objectCreator ? $objectCreator($requestHandlerClass) : new $requestHandlerClass;

            $middlewares = [];
            foreach ($annotation->middlewares as $middlewareClass) {
                $middlewares[] = $objectCreator ? $objectCreator($middlewareClass) : new $middlewareClass;
            }

            $routes[] = new BaseRoute(
                $annotation->name,
                $annotation->path,
                $annotation->methods,
                $requestHandler,
                $middlewares,
                $annotation->attributes
            );
        }

        return $routes;
    }

    /**
     * Discovers routes in the given destination
     *
     * @param string $destination
     *
     * @return void
     */
    public function discover(string $destination) : void
    {
        $annotations = $this->findAnnotations($destination, Route::class);

        foreach ($annotations as $annotation) {
            $this->discoveredAnnotations->insert($annotation, $annotation->priority);
        }
    }

    /**
     * Finds annotations in the given destination
     *
     * @param string $destination
     * @param string $name
     *
     * @return array
     */
    private function findAnnotations(string $destination, string $name) : array
    {
        $classes = $this->findClasses($destination);
        $annotations = [];

        foreach ($classes as $class) {
            $class = new ReflectionClass($class);
            $annotation = $this->annotationReader->getClassAnnotation($class, $name);

            if ($annotation instanceof $name) {
                $annotation->source = $class;
                $annotations[] = $annotation;
            }
        }

        return $annotations;
    }

    /**
     * Finds classes in the given destination
     *
     * @param string $destination
     *
     * @return array
     */
    private function findClasses(string $destination) : array
    {
        $files = $this->findFiles($destination);
        $classes = get_declared_classes();

        foreach ($files as $file) {
            require_once $file->getRealPath();
        }

        return array_diff(get_declared_classes(), $classes);
    }

    /**
     * Finds files in the given destination
     *
     * @param string $destination
     *
     * @return array
     */
    private function findFiles(string $destination) : array
    {
        $directory = new RecursiveDirectoryIterator($destination);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, '/\.php$/');

        return iterator_to_array($files);
    }
}
