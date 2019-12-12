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
use Sunrise\Http\Router\OpenApi\AbstractObject;

/**
 * Import functions
 */
use function array_merge;
use function array_walk_recursive;

/**
 * AbstractAnnotation
 */
abstract class AbstractAnnotation extends AbstractObject
{

    /**
     * {@inheritDoc}
     */
    public function getComponentObjects(SimpleAnnotationReader $annotationReader) : array
    {
        $fields = $this->getFields();
        $objects = [];

        array_walk_recursive($fields, function ($value) use ($annotationReader, &$objects) {
            if ($value instanceof AbstractAnnotation) {
                $objects = array_merge($objects, $value->getComponentObjects($annotationReader));
                return;
            }

            if ($value instanceof AbstractReference) {
                $object = $value->getAnnotation($annotationReader);
                $objects[] = $object;
                $objects = array_merge($objects, $object->getComponentObjects($annotationReader));
                return;
            }
        });

        return $objects;
    }
}
