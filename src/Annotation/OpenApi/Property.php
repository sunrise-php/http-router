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
 * @Target({"ANNOTATION"})
 */
final class Property
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
     * @Enum({"string", "number", "integer", "boolean", "array", "object"})
     *
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $ref;

    /**
     * @var bool
     */
    public $deprecated = false;

    /**
     * @var bool
     */
    public $readOnly = false;

    /**
     * @var bool
     */
    public $writeOnly = false;

    /**
     * @var bool
     */
    public $nullable = false;

    /**
     * @return array
     */
    public function toArray() : array
    {
        $result = [
            'type' => $this->type,
            'deprecated' => $this->deprecated,
            'readOnly' => $this->readOnly,
            'writeOnly' => $this->writeOnly,
            'nullable' => $this->nullable,
        ];

        if (isset($this->ref)) {
            $result['$ref'] = $this->ref;
        }

        return $result;
    }
}
