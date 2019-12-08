<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Annotation\OpenApi;

/**
 * Import classes
 */
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Import functions
 */
use function sprintf;

/**
 * AbstractReference
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#reference-object
 */
abstract class AbstractReference implements AnnotationInterface
{

    /**
     * @Required
     *
     * @var string
     */
    public $name;

    /**
     * @Required
     *
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $property;

    /**
     * @return string
     */
    abstract public function getAnnotationName() : string;

    /**
     * @return string
     */
    abstract public function getComponentName() : string;

    /**
     * @param SimpleAnnotationReader $annotationReader
     *
     * @return null|AbstractAnnotation
     */
    public function getAnnotation(SimpleAnnotationReader $annotationReader) : ?AbstractAnnotation
    {
        if (isset($this->method)) {
            return $annotationReader->getMethodAnnotation(
                new ReflectionMethod($this->class, $this->method),
                $this->getAnnotationName()
            );
        }

        if (isset($this->property)) {
            return $annotationReader->getPropertyAnnotation(
                new ReflectionProperty($this->class, $this->property),
                $this->getAnnotationName()
            );
        }

        return $annotationReader->getClassAnnotation(
            new ReflectionClass($this->class),
            $this->getAnnotationName()
        );
    }

    /**
     * @return string
     */
    public function getComponentPath() : string
    {
        return sprintf('#/components/%s/%s', $this->getComponentName(), $this->name);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        return ['$ref' => $this];
    }
}
