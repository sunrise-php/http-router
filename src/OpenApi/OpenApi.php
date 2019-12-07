<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\OpenApi;

/**
 * Import classes
 */
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Sunrise\Http\Router\Annotation\OpenApi\AnnotationInterface;
use Sunrise\Http\Router\Annotation\OpenApi\Operation;
use Sunrise\Http\Router\Annotation\OpenApi\Parameter;
use Sunrise\Http\Router\RouteInterface;
use ReflectionClass;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_parse;
use function array_walk_recursive;
use function str_replace;
use function strtolower;

/**
 * OpenApi
 */
class OpenApi
{

    /**
     * Version of OpenAPI specification
     *
     * @var string
     */
    public const VERSION = '3.0.2';

    /**
     * @var SimpleAnnotationReader
     */
    private $annotationReader;

    /**
     * @var RouteInterface[]
     */
    private $routes = [];

    /**
     * @var string
     */
    private $title = 'REST API';

    /**
     * @var string
     */
    private $version = '0.0.1';

    /**
     * @var array
     */
    private $documentation = [];

    /**
     * Constructor of the class
     */
    public function __construct()
    {
        $this->annotationReader = new SimpleAnnotationReader();
        $this->annotationReader->addNamespace('Sunrise\Http\Router\Annotation');
    }

    /**
     * @param RouteInterface ...$routes
     *
     * @return void
     */
    public function addRoute(RouteInterface ...$routes) : void
    {
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }
    }

    /**
     * @param string $title
     *
     * @return void
     */
    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    /**
     * @param string $version
     *
     * @return void
     */
    public function setVersion(string $version) : void
    {
        $this->version = $version;
    }

    /**
     * @return void
     */
    public function describe() : void
    {
        $this->documentation['openapi'] = self::VERSION;
        $this->documentation['info']['title'] = $this->title;
        $this->documentation['info']['version'] = $this->version;

        foreach ($this->routes as $route) {
            $path = $this->createPatternedPathFromRoute($route);
            $operation = $this->createOperationAnnotationFromRoute($route);

            foreach ($route->getMethods() as $method) {
                $method = strtolower($method);

                $this->documentation['paths'][$path][$method]['operationId'] = $route->getName();
                $this->documentation['paths'][$path][$method] += $operation->toArray();
            }
        }

        $this->handleReferences();
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->documentation;
    }

    /**
     * @param RouteInterface $route
     *
     * @return string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#patterned-fields
     */
    private function createPatternedPathFromRoute(RouteInterface $route) : string
    {
        $path = $route->getPath();
        $attributes = path_parse($path);

        foreach ($attributes as $attribute) {
            $path = str_replace($attribute['raw'], '{' . $attribute['name'] . '}', $path);
        }

        return str_replace(['(', ')'], '', $path);
    }

    /**
     * @param RouteInterface $route
     *
     * @return Operation
     */
    private function createOperationAnnotationFromRoute(RouteInterface $route) : Operation
    {
        $target = new ReflectionClass($route->getRequestHandler());
        $operation = $this->annotationReader->getClassAnnotation($target, Operation::class) ?? new Operation();
        $attributes = path_parse($route->getPath());

        foreach ($attributes as $attribute) {
            $parameter = new Parameter();
            $parameter->in = 'path';
            $parameter->name = $attribute['name'];
            $parameter->required = !$attribute['isOptional'];
            $operation->parameters[] = $parameter;
        }

        return $operation;
    }

    /**
     * @return void
     */
    private function handleReferences() : void
    {
        array_walk_recursive($this->documentation, function (&$value) {
            if (!($value instanceof AnnotationInterface)) {
                return;
            }

            $ref = $value;
            $value = $ref->getComponentPath();
            $component = &$this->documentation['components'][$ref->getComponentName()];

            if (isset($component[$ref->name])) {
                return;
            }

            $annotation = $ref->getAnnotation($this->annotationReader);

            if (empty($annotation)) {
                return;
            }

            $component[$ref->name] = $annotation->toArray();
        });
    }
}
