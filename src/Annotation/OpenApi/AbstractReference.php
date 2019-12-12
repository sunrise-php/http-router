<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Annotation\OpenApi\Reference;

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
    private static $objects = [];

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
    abstract protected function getTarget() : string;

    /**
     * @param SimpleAnnotationReader $annotationReader
     *
     * @return null|ComponentObjectInterface
     */
    public function getComponentObject(SimpleAnnotationReader $annotationReader) : ?ComponentObjectInterface
    {
        $key = hash('md5', $this->class . $this->method . $this->property . $this->getTarget());

        if (isset(self::$objects[$key])) {
            $this->target = self::$objects[$key];
            return $this->target;
        } elseif (isset($this->method)) {
            $source = new ReflectionMethod($this->class, $this->method);
            $this->target = $annotationReader->getMethodAnnotation($source, $this->getTarget());
        } elseif (isset($this->property)) {
            $source = new ReflectionProperty($this->class, $this->property);
            $this->target = $annotationReader->getPropertyAnnotation($source, $this->getTarget());
        } else {
            $source = new ReflectionClass($this->class);
            $this->target = $annotationReader->getClassAnnotation($source, $this->getTarget());
        }

        self::$objects[$key] = $this->target;
        return $this->target;
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
