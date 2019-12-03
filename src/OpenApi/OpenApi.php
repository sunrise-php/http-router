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
use Sunrise\Http\Router\RouteCollectionInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\OpenApi\Annotation\OpenApi\Operation;
use Sunrise\Http\Router\OpenApi\Annotation\OpenApi\Parameter;
use ReflectionClass;

/**
 * Import functions
 */
use function Sunrise\Http\Router\path_parse;
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
    private $schema = [];

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
        $this->annotationReader->addNamespace('Sunrise\Http\Router\OpenApi\Annotation');

        $this->schema['openapi'] = '3.0.2';
        $this->schema['info']['title'] = $title;
        $this->schema['info']['version'] = $version;

        // auto build...
        $this->build();
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->schema;
    }

    /**
     * @return void
     */
    private function build() : void
    {
        foreach ($this->routes->all() as $route) {
            $path = $this->getPathFromRoute($route);
            $operation = $this->getOperationFromRoute($route);

            foreach ($route->getMethods() as $method) {
                $method = strtolower($method);

                $this->schema['paths'][$path][$method] = $operation->toArray();
            }
        }
    }

    /**
     * @param RouteInterface $route
     *
     * @return Operation
     */
    private function getOperationFromRoute(RouteInterface $route) : Operation
    {
        $source = new ReflectionClass($route->getRequestHandler());

        $operation = $this->annotationReader->getClassAnnotation($source, Operation::class);
        $operation = $operation ?? new Operation();

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
     * @param RouteInterface $route
     *
     * @return string
     */
    private function getPathFromRoute(RouteInterface $route) : string
    {
        $path = $route->getPath();
        $attributes = path_parse($path);

        foreach ($attributes as $attribute) {
            $path = str_replace($attribute['raw'], '{' . $attribute['name'] . '}', $path);
        }

        return str_replace(['(', ')'], '', $path);
    }
}
