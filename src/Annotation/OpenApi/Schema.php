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
 * @link https://json-schema.org/draft/2019-09/json-schema-validation.html
 */
final class Schema extends AbstractAnnotation implements SchemaInterface
{

    /**
     * @var \Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface
     */
    public $additionalProperties;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface>
     */
    public $allOf;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface>
     */
    public $anyOf;

    /**
     * @var mixed
     */
    public $default;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-schemadeprecated
     */
    public $deprecated;

    /**
     * @var string
     */
    public $description;

    /**
     * @var array
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.1.2
     */
    public $enum;

    /**
     * @var mixed
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-schemaexample
     */
    public $example;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.2.3
     */
    public $exclusiveMaximum;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.2.5
     */
    public $exclusiveMinimum;

    /**
     * @var string
     */
    public $format;

    /**
     * @var \Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface
     */
    public $items;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.2.2
     */
    public $maximum;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.4.1
     */
    public $maxItems;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.3.1
     */
    public $maxLength;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.5.1
     */
    public $maxProperties;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.2.4
     */
    public $minimum;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.4.2
     */
    public $minItems;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.3.2
     */
    public $minLength;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.5.2
     */
    public $minProperties;

    /**
     * @var int
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.2.1
     */
    public $multipleOf;

    /**
     * @var \Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface
     */
    public $not;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-schemanullable
     */
    public $nullable;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface>
     */
    public $oneOf;

    /**
     * @var string
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.3.3
     */
    public $pattern;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface>
     */
    public $properties;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-schemareadonly
     */
    public $readOnly;

    /**
     * @var array<string>
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.5.3
     */
    public $required;

    /**
     * @var string
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.9.1
     */
    public $title;

    /**
     * @var string
     */
    public $type;

    /**
     * @var bool
     *
     * @link https://json-schema.org/draft/2019-09/json-schema-validation.html#rfc.section.6.4.3
     */
    public $uniqueItems;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-schemawriteonly
     */
    public $writeOnly;

    /**
     * @var array
     */
    private $simpleFields = [
        'default',
        'deprecated',
        'description',
        'enum',
        'example',
        'exclusiveMaximum',
        'exclusiveMinimum',
        'format',
        'maximum',
        'maxItems',
        'maxLength',
        'maxProperties',
        'minimum',
        'minItems',
        'minLength',
        'minProperties',
        'multipleOf',
        'nullable',
        'pattern',
        'readOnly',
        'required',
        'title',
        'type',
        'uniqueItems',
        'writeOnly',
    ];

    /**
     * @var array
     */
    private $annotatedFields = [
        'additionalProperties',
        'items',
        'not',
    ];

    /**
     * @var array
     */
    private $mappedFields = [
        'allOf',
        'anyOf',
        'oneOf',
        'properties',
    ];

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        return $this->getSimpleFields() + $this->getAnnotatedFields() + $this->getMappedFields();
    }

    /**
     * @return array
     */
    private function getSimpleFields() : array
    {
        $result = [];
        foreach ($this->simpleFields as $fieldName) {
            if (isset($this->{$fieldName})) {
                $result[$fieldName] = $this->{$fieldName};
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getAnnotatedFields() : array
    {
        $result = [];
        foreach ($this->annotatedFields as $fieldName) {
            if (isset($this->{$fieldName})) {
                $result[$fieldName] = $this->{$fieldName}->toArray();
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getMappedFields() : array
    {
        $result = [];
        foreach ($this->mappedFields as $fieldName) {
            if (isset($this->{$fieldName})) {
                foreach ($this->{$fieldName} as $i => $value) {
                    $result[$fieldName][$i] = $value->toArray();
                }
            }
        }

        return $result;
    }
}
