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
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#header-object
 */
final class Header extends AbstractAnnotation implements HeaderInterface
{

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var bool
     */
    public $required = false;

    /**
     * @var bool
     */
    public $deprecated = false;

    /**
     * @var bool
     */
    public $allowEmptyValue = false;

    /**
     * @var bool
     */
    public $allowReserved = false;

    /**
     * @var \Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface
     */
    public $schema;

    /**
     * @var mixed
     */
    public $example;

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        $result = [
            'description' => $this->description,
            'required' => $this->required,
            'deprecated' => $this->deprecated,
            'allowEmptyValue' => $this->allowEmptyValue,
            'allowReserved' => $this->allowReserved,
        ];

        if (isset($this->schema)) {
            $result['schema'] = $this->schema->toArray();
        }

        if (isset($this->example)) {
            $result['example'] = $this->example;
        }

        return $result;
    }
}
