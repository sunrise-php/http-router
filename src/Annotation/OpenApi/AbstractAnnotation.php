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
 * Import functions
 */
use function is_array;

/**
 * AbstractAnnotation
 */
abstract class AbstractAnnotation implements AnnotationInterface
{

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        $fields = $this->getFields();

        $result = [];
        foreach ($fields as $field => $value) {
            if (!is_array($value)) {
                $result[$field] = ($value instanceof AnnotationInterface) ? $value->toArray() : $value;
                continue;
            }

            foreach ($value as $key => $item) {
                $result[$field][$key] = ($item instanceof AnnotationInterface) ? $item->toArray() : $item;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getFields() : array
    {
        $result = [];
        foreach ($this as $field => $value) {
            if (isset($value)) {
                $result[$field] = $value;
            }
        }

        return $result;
    }
}
