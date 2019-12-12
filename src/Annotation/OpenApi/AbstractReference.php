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
use Sunrise\Http\Router\OpenApi\ComponentObjectInterface;

/**
 * Import functions
 */
use function hash;

/**
 * AbstractReference
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#reference-object
 */
abstract class AbstractReference
{

    /**
     * @var array
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
     * @var null|ComponentObjectInterface
     */
    private $target;

    /**
     * @return string
     */
    abstract protected function getAnnotationName() : string;

    /**
     * @param SimpleAnnotationReader $annotationReader
     *
     * @return null|ComponentObjectInterface
     */
    public function getAnnotation(SimpleAnnotationReader $annotationReader) : ?ComponentObjectInterface
    {
        $key = hash(
            'md5',
            $this->class .
            $this->method .
            $this->property .
            $this->getAnnotationName()
        );

        $this->target =& self::$cache[$key];

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        if (isset($this->method)) {
            return $this->target = $annotationReader->getMethodAnnotation(
                new ReflectionMethod($this->class, $this->method),
                $this->getAnnotationName()
            );
        }

        if (isset($this->property)) {
            return $this->target = $annotationReader->getPropertyAnnotation(
                new ReflectionProperty($this->class, $this->property),
                $this->getAnnotationName()
            );
        }

        return $this->target = $annotationReader->getClassAnnotation(
            new ReflectionClass($this->class),
            $this->getAnnotationName()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        if (isset($this->target)) {
            return ['$ref' => sprintf(
                '#/components/%s/%s',
                $this->target->getComponentName(),
                $this->target->getReferenceName()
            )];
        }

        return ['$ref' => 'undefined'];
    }
}
