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
use Sunrise\Http\Router\Annotation\OpenApi\Operation;
use Sunrise\Http\Router\Annotation\OpenApi\Parameter;
use Sunrise\Http\Router\Annotation\OpenApi\Schema;
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteInterface;
use ReflectionClass;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_parse;
use function array_walk_recursive;
use function class_exists;
use function str_replace;
use function strtolower;

/**
 * OpenApi
 */
class OpenApi
{

    /**
     * @var RouteCollectionInterface
     */
    private $routes;

    /**
     * @var SimpleAnnotationReader
     */
    private $annotationReader;

    /**
     * @var array
     */
    private $description = [];

    /**
     * Constructor of the class
     *
     * @param string $title
     * @param string $version
     * @param RouteCollectionInterface $routes
     */
    public function __construct(string $title, string $version, RouteCollectionInterface $routes)
    {
        $this->routes = $routes;

        $this->annotationReader = new SimpleAnnotationReader();
        $this->annotationReader->addNamespace('Sunrise\Http\Router\Annotation');

        $this->description['openapi'] = '3.0.2';
        $this->description['info']['title'] = $title;
        $this->description['info']['version'] = $version;

        // auto build...
        $this->build();
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->description;
    }

    /**
     * @return void
     */
    private function build() : void
    {
        foreach ($this->routes->all() as $route) {
            $path = $this->createPatternedPathFromRoute($route);
            $operation = $this->createOperationAnnotationFromRoute($route);

            foreach ($route->getMethods() as $method) {
                $method = strtolower($method);

                $this->description['paths'][$path][$method]['operationId'] = $route->getName();
                $this->description['paths'][$path][$method] += $operation->toArray();
            }
        }

        $this->handleComponentSchemas();
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
        $source = $route->getRequestHandler();

        $operation = $this->annotationReader->getClassAnnotation(new ReflectionClass($source), Operation::class);

        if (!$operation) {
            $operation = new Operation();
        }

        $attributes = path_parse($route->getPath());

        foreach ($attributes as $attribute) {
            $parameter = new Parameter();
            $parameter->in = 'path';
            $parameter->name = $attribute['name'];
            $parameter->required = $attribute['isOptional'];

            $operation->parameters[] = $parameter;
        }

        return $operation;
    }

    /**
     * @return void
     */
    private function handleComponentSchemas() : void
    {
        array_walk_recursive($this->description, function (&$value, $key) {
            if (!('$ref' === $key && null !== $value && class_exists($value))) {
                return;
            }

            // the schema already exists...
            if (isset($this->description['components']['schemas'][$value])) {
                return;
            }

            $schema = $this->annotationReader->getClassAnnotation(new ReflectionClass($value), Schema::class);

            if (!$schema) {
                return;
            }

            $this->description['components']['schemas'][$value]['type'] = 'object';
            $this->description['components']['schemas'][$value] += $schema->toArray();

            $value = '#/components/schemas/' . $value;
        });
    }
}
