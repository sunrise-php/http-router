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
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#parameter-object
 */
final class Parameter extends AbstractAnnotation implements ParameterInterface
{

    /**
     * @Required
     *
     * @Enum({"cookie", "header", "query"})
     *
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterin
     */
    public $in;

    /**
     * @Required
     *
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parametername
     */
    public $name;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterdescription
     */
    public $description = '';

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterrequired
     */
    public $required = false;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterdeprecated
     */
    public $deprecated = false;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterallowemptyvalue
     */
    public $allowEmptyValue = false;

    /**
     * @Enum({"matrix", "label", "form", "simple", "spaceDelimited", "pipeDelimited", "deepObject"})
     *
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterstyle
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#style-values
     */
    public $style;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterexplode
     */
    public $explode;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterallowreserved
     */
    public $allowReserved = false;

    /**
     * @var \Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterschema
     */
    public $schema;

    /**
     * @var mixed
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterexample
     */
    public $example;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\ExampleInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterexamples
     */
    public $examples = [];

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\MediaTypeInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parametercontent
     */
    public $content = [];

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        $result = [
            'in' => $this->in,
            'name' => $this->name,
            'description' => $this->description,
            'required' => $this->required,
            'deprecated' => $this->deprecated,
            'allowEmptyValue' => $this->allowEmptyValue,
            'allowReserved' => $this->allowReserved,
        ];

        if (isset($this->style)) {
            $result['style'] = $this->style;
        }

        if (isset($this->explode)) {
            $result['explode'] = $this->explode;
        }

        if (isset($this->schema)) {
            $result['schema'] = $this->schema->toArray();
        }

        if (isset($this->example)) {
            $result['example'] = $this->example;
        }

        foreach ($this->examples as $key => $value) {
            $result['examples'][$key] = $value->toArray();
        }

        foreach ($this->content as $key => $value) {
            $result['content'][$key] = $value->toArray();
        }

        return $result;
    }
}
