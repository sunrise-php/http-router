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
 * Import functions
 */
use function is_array;

/**
 * AbstractObject
 */
abstract class AbstractObject implements ObjectInterface
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
                $result[$field] = ($value instanceof ObjectInterface) ? $value->toArray() : $value;
                continue;
            }

            foreach ($value as $key => $item) {
                $result[$field][$key] = ($item instanceof ObjectInterface) ? $item->toArray() : $item;
            }
        }

        return $result;
    }

    /**
     * Gets only filled fields from the object
     *
     * @return array
     */
    private function getFields() : array
    {
        $result = [];
        foreach ($this as $field => $value) {
            // not set value...
            if (null === $value) {
                continue;
            }

            $result[$field] = $value;
        }

        return $result;
    }
}
