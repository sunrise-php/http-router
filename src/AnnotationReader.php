<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Sunrise\Http\Router\Exception\LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Reflector;

/**
 * Import functions
 */
use function class_exists;
use function sprintf;

/**
 * Import constants
 */
use const PHP_MAJOR_VERSION;

/**
 * AnnotationReader
 *
 * @since 3.0.0
 */
final class AnnotationReader
{

    /**
     * @var \Doctrine\Common\Annotations\Reader|null
     */
    private ?\Doctrine\Common\Annotations\Reader $annotationReader = null;

    /**
     * Sets the given annotation reader to the reader
     *
     * @param \Doctrine\Common\Annotations\Reader|null $annotationReader
     *
     * @return void
     */
    public function setAnnotationReader(?\Doctrine\Common\Annotations\Reader $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Uses the default annotation reader
     *
     * @return void
     *
     * @throws LogicException
     *         If the "doctrine/annotations" package isn't installed.
     */
    public function useDefaultAnnotationReader(): void
    {
        if (!class_exists(\Doctrine\Common\Annotations\AnnotationReader::class)) {
            throw new LogicException(
                'The annotations reading logic requires an uninstalled "doctrine/annotations" package, ' .
                'run the command "composer install doctrine/annotations" and try again'
            );
        }

        $this->setAnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader());
    }

    /**
     * Gets annotations from the given class or method by the given annotation name
     *
     * @param ReflectionClass|ReflectionMethod $classOrMethod
     * @param class-string<T> $annotationName
     *
     * @return list<T>
     *
     * @throws LogicException
     *         If the given reflection isn't supported.
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     *
     * @template T
     */
    public function getClassOrMethodAnnotations(Reflector $classOrMethod, string $annotationName): array
    {
        if ($classOrMethod instanceof ReflectionClass) {
            return $this->getClassAnnotations($classOrMethod, $annotationName);
        }

        if ($classOrMethod instanceof ReflectionMethod) {
            return $this->getMethodAnnotations($classOrMethod, $annotationName);
        }

        throw new LogicException(sprintf(
            'The %s method only handles class or method reflection',
            __METHOD__
        ));
    }

    /**
     * Gets annotations from the given class by the given annotation name
     *
     * @param ReflectionClass $class
     * @param class-string<T> $annotationName
     *
     * @return list<T>
     *
     * @template T
     */
    public function getClassAnnotations(ReflectionClass $class, string $annotationName): array
    {
        $result = [];

        if (PHP_MAJOR_VERSION === 8) {
            /** @var ReflectionAttribute[] */
            $attributes = $class->getAttributes($annotationName);
            foreach ($attributes as $attribute) {
                /** @var T */
                $result[] = $attribute->newInstance();
            }
        }

        if (isset($this->annotationReader)) {
            $annotations = $this->annotationReader->getClassAnnotations($class);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof $annotationName) {
                    $result[] = $annotation;
                }
            }
        }

        return $result;
    }

    /**
     * Gets annotations from the given method by the given annotation name
     *
     * @param ReflectionMethod $method
     * @param class-string<T> $annotationName
     *
     * @return list<T>
     *
     * @template T
     */
    public function getMethodAnnotations(ReflectionMethod $method, string $annotationName): array
    {
        $result = [];

        if (PHP_MAJOR_VERSION === 8) {
            /** @var ReflectionAttribute[] */
            $attributes = $method->getAttributes($annotationName);
            foreach ($attributes as $attribute) {
                /** @var T */
                $result[] = $attribute->newInstance();
            }
        }

        if (isset($this->annotationReader)) {
            $annotations = $this->annotationReader->getMethodAnnotations($method);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof $annotationName) {
                    $result[] = $annotation;
                }
            }
        }

        return $result;
    }
}
