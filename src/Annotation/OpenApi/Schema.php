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
 * @Annotation
 *
 * @Target({"ANNOTATION", "CLASS"})
 */
final class Schema
{

    /**
     * @var string
     */
    public $ref;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array<Sunrise\Http\Router\Annotation\OpenApi\Property>
     */
    public $properties = [];

    /**
     * @return array
     */
    public function toArray() : array
    {
        $result = [];

        if (isset($this->ref)) {
            $result['$ref'] = $this->ref;
        }

        if (isset($this->type)) {
            $result['type'] = $this->type;
        }

        foreach ($this->properties as $value) {
            $result['properties'][$value->name] = $value->toArray();
        }

        return $result;
    }
}
