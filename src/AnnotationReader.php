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
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Doctrine\Common\Annotations\Reader as DoctrineAnnotationReaderInterface;
use Sunrise\Http\Router\Exception\LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Reflector;

/**
 * Import functions
 */
use function class_exists;

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
     * @var DoctrineAnnotationReaderInterface|null
     */
    private ?DoctrineAnnotationReaderInterface $annotationReader = null;

    /**
     * Sets the given annotation reader to the reader
     *
     * @param DoctrineAnnotationReaderInterface|null $annotationReader
     *
     * @return void
     */
    public function setAnnotationReader(?DoctrineAnnotationReaderInterface $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Sets the default annotation reader to the reader
     *
     * @return void
     *
     * @throws LogicException
     *         If the "doctrine/annotations" package isn't installed.
     */
    public function useDefaultAnnotationReader(): void
    {
        if (!class_exists(DoctrineAnnotationReader::class)) {
            throw new LogicException(
                'The annotations reading logic requires an uninstalled "doctrine/annotations" package, ' .
                'run the command "composer install doctrine/annotations" and try again'
            );
        }

        $this->setAnnotationReader(new DoctrineAnnotationReader());
    }

    /**
     * Gets annotations by the given name from the given class or method
     *
     * @param ReflectionClass|ReflectionMethod $classOrMethod
     * @param class-string<T> $annotationName
     *
     * @return list<T>
     *
     * @template T
     */
    public function getAnnotations(Reflector $classOrMethod, string $annotationName): array
    {
        $result = [];

        if (PHP_MAJOR_VERSION === 8) {
            /** @var ReflectionAttribute[] */
            $attributes = $classOrMethod->getAttributes($annotationName);
            foreach ($attributes as $attribute) {
                /** @var T */
                $result[] = $attribute->newInstance();
            }
        }

        if (isset($this->annotationReader)) {
            $annotations = ($classOrMethod instanceof ReflectionClass) ?
                $this->annotationReader->getClassAnnotations($classOrMethod) :
                $this->annotationReader->getMethodAnnotations($classOrMethod);

            foreach ($annotations as $annotation) {
                if ($annotation instanceof $annotationName) {
                    $result[] = $annotation;
                }
            }
        }

        return $result;
    }
}
