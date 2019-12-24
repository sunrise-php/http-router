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
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Sunrise\Http\Router\Exception\InvalidAnnotationParameterException;

/**
 * Import functions
 */
use function hash;
use function sprintf;
use function class_exists;
use function method_exists;
use function property_exists;
use function get_called_class;

/**
 * AbstractAnnotationReference
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#reference-object
 */
abstract class AbstractAnnotationReference implements ObjectInterface
{

    /**
     * Storage for referenced objects
     *
     * @var ComponentObjectInterface[]
     */
    private static $cache = [];

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
     * @var ComponentObjectInterface
     */
    private $referencedObject;

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        // theoretically this condition will never be confirmed...
        if (null === $this->referencedObject) {
            return ['$ref' => 'undefined'];
        }

        return ['$ref' => sprintf(
            '#/components/%s/%s',
            $this->referencedObject->getComponentName(),
            $this->referencedObject->getReferenceName()
        )];
    }

    /**
     * The child class must return a class name that implements the `ComponentObjectInterface` interface
     *
     * @return string
     */
    abstract public function getAnnotationName() : string;

    /**
     * Tries to find a referenced object that implements the `ComponentObjectInterface` interface
     *
     * @param SimpleAnnotationReader $annotationReader
     *
     * @return ComponentObjectInterface
     *
     * @throws InvalidAnnotationParameterException
     */
    public function getAnnotation(SimpleAnnotationReader $annotationReader) : ComponentObjectInterface
    {
        $key = hash(
            'md5',
            $this->class .
            $this->method .
            $this->property .
            $this->getAnnotationName()
        );

        $this->referencedObject =& self::$cache[$key];

        if (isset($this->referencedObject)) {
            return $this->referencedObject;
        }

        if (isset($this->method)) {
            return $this->referencedObject = $this->getMethodAnnotation($annotationReader);
        }

        if (isset($this->property)) {
            return $this->referencedObject = $this->getPropertyAnnotation($annotationReader);
        }

        return $this->referencedObject = $this->getClassAnnotation($annotationReader);
    }

    /**
     * Proxy to `SimpleAnnotationReader::getMethodAnnotation()` with validation
     *
     * @param SimpleAnnotationReader $annotationReader
     *
     * @return ComponentObjectInterface
     *
     * @throws InvalidAnnotationParameterException
     *
     * @see SimpleAnnotationReader::getMethodAnnotation()
     */
    private function getMethodAnnotation(SimpleAnnotationReader $annotationReader) : ComponentObjectInterface
    {
        if (!method_exists($this->class, $this->method)) {
            $message = 'Annotation %s refers to non-existent method %s::%s()';
            throw new InvalidAnnotationParameterException(
                sprintf($message, get_called_class(), $this->class, $this->method)
            );
        }

        $object = $annotationReader->getMethodAnnotation(
            new ReflectionMethod($this->class, $this->method),
            $this->getAnnotationName()
        );

        if (null === $object) {
            $message = 'Method %s::%s() does not contain the annotation %s';
            throw new InvalidAnnotationParameterException(
                sprintf($message, $this->class, $this->method, $this->getAnnotationName())
            );
        }

        return $object;
    }

    /**
     * Proxy to `SimpleAnnotationReader::getPropertyAnnotation()` with validation
     *
     * @param SimpleAnnotationReader $annotationReader
     *
     * @return ComponentObjectInterface
     *
     * @throws InvalidAnnotationParameterException
     *
     * @see SimpleAnnotationReader::getPropertyAnnotation()
     */
    private function getPropertyAnnotation(SimpleAnnotationReader $annotationReader) : ComponentObjectInterface
    {
        if (!property_exists($this->class, $this->property)) {
            $message = 'Annotation %s refers to non-existent property %s::$%s';
            throw new InvalidAnnotationParameterException(
                sprintf($message, get_called_class(), $this->class, $this->property)
            );
        }

        $object = $annotationReader->getPropertyAnnotation(
            new ReflectionProperty($this->class, $this->property),
            $this->getAnnotationName()
        );

        if (null === $object) {
            $message = 'Property %s::$%s does not contain the annotation %s';
            throw new InvalidAnnotationParameterException(
                sprintf($message, $this->class, $this->property, $this->getAnnotationName())
            );
        }

        return $object;
    }

    /**
     * Proxy to `SimpleAnnotationReader::getClassAnnotation()` with validation
     *
     * @param SimpleAnnotationReader $annotationReader
     *
     * @return ComponentObjectInterface
     *
     * @throws InvalidAnnotationParameterException
     *
     * @see SimpleAnnotationReader::getClassAnnotation()
     */
    private function getClassAnnotation(SimpleAnnotationReader $annotationReader) : ComponentObjectInterface
    {
        if (!class_exists($this->class)) {
            $message = 'Annotation %s refers to non-existent class %s';
            throw new InvalidAnnotationParameterException(
                sprintf($message, get_called_class(), $this->class)
            );
        }

        $object = $annotationReader->getClassAnnotation(
            new ReflectionClass($this->class),
            $this->getAnnotationName()
        );

        if (null === $object) {
            $message = 'Class %s does not contain the annotation %s';
            throw new InvalidAnnotationParameterException(
                sprintf($message, $this->class, $this->getAnnotationName())
            );
        }

        return $object;
    }
}
