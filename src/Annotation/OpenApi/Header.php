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
use Sunrise\Http\Router\OpenApi\ComponentObjectInterface;

/**
 * Import functions
 */
use function spl_object_hash;

/**
 * @Annotation
 *
 * @Target({"ALL"})
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#header-object
 */
final class Header extends AbstractAnnotation implements HeaderInterface, ComponentObjectInterface
{

    /**
     * {@inheritDoc}
     */
    protected const IGNORE_FIELDS = ['refName'];

    /**
     * @var string
     */
    public $refName;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterdescription
     */
    public $description;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterrequired
     */
    public $required;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterdeprecated
     */
    public $deprecated;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterallowemptyvalue
     */
    public $allowEmptyValue;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-parameterallowreserved
     */
    public $allowReserved;

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
    public $examples;

    /**
     * {@inheritDoc}
     */
    public function getComponentName() : string
    {
        return 'headers';
    }

    /**
     * {@inheritDoc}
     */
    public function getReferenceName() : string
    {
        return $this->refName ?? spl_object_hash($this);
    }
}
