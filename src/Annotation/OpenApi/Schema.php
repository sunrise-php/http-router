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
 * @Target({"ALL"})
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#schema-object
 */
final class Schema extends AbstractAnnotation implements SchemaInterface
{

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $format;

    /**
     * @var mixed
     */
    public $default;

    /**
     * @var mixed
     */
    public $example;

    /**
     * @var \Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface
     */
    public $items;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface>
     */
    public $properties = [];

    /**
     * @var bool
     */
    public $nullable = false;

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
    public $deprecated = false;

    /**
     * @var array
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html
     */
    public $validation = [];

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        $result = [
            'description' => $this->description,
            'nullable' => $this->nullable,
            'readOnly' => $this->readOnly,
            'writeOnly' => $this->writeOnly,
            'deprecated' => $this->deprecated,
        ];

        if (isset($this->type)) {
            $result['type'] = $this->type;
        }

        if (isset($this->format)) {
            $result['format'] = $this->format;
        }

        if (isset($this->default)) {
            $result['default'] = $this->default;
        }

        if (isset($this->example)) {
            $result['example'] = $this->example;
        }

        if (isset($this->items)) {
            $result['items'] = $this->items->toArray();
        }

        foreach ($this->properties as $key => $value) {
            $result['properties'][$key] = $value->toArray();
        }

        $result += $this->validation;

        return $result;
    }
}
