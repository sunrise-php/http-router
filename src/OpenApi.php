<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Sunrise\Http\Router\Annotation\OpenApi\Operation;
use Sunrise\Http\Router\Annotation\OpenApi\Parameter;
use Sunrise\Http\Router\Annotation\OpenApi\Schema;
use Sunrise\Http\Router\OpenApi\Info;
use ReflectionClass;

/**
 * Import functions
 */
use function strtolower;

/**
 * OpenApi
 */
class OpenApi extends OpenApi\OpenApi
{

    /**
     * @var string
     */
    public const VERSION = '3.0.2';

    /**
     * @var SimpleAnnotationReader
     */
    private $annotationReader;

    /**
     * Constructor of the class
     *
     * @param Info $info
     */
    public function __construct(Info $info)
    {
        parent::__construct(self::VERSION, $info);

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
            $path = path_plain($route->getPath());
            $operation = $this->createOperation($route);

            foreach ($route->getMethods() as $method) {
                $method = strtolower($method);

                $this->paths[$path][$method] = $operation;
            }
        }
    }

    /**
     * @param RouteInterface $route
     *
     * @return Operation
     */
    private function createOperation(RouteInterface $route) : Operation
    {
        $target = new ReflectionClass($route->getRequestHandler());

        $operation = $this->annotationReader->getClassAnnotation($target, Operation::class) ?? new Operation();

        $operation->operationId = $operation->operationId ?? $route->getName();

        $this->addComponentObject(...$operation->getComponentObjects($this->annotationReader));

        $attributes = path_parse($route->getPath());

        foreach ($attributes as $attribute) {
            $parameter = new Parameter();
            $parameter->in = 'path';
            $parameter->name = $attribute['name'];
            $parameter->required = !$attribute['isOptional'];

            if (isset($attribute['pattern'])) {
                $parameter->schema = new Schema();
                $parameter->schema->type = 'string';
                $parameter->schema->pattern = $attribute['pattern'];
            }

            $operation->parameters[] = $parameter;
        }

        return $operation;
    }
}
