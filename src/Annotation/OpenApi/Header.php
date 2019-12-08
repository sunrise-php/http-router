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
    public $description;

    /**
     * @var bool
     */
    public $required;

    /**
     * @var bool
     */
    public $deprecated;

    /**
     * @var bool
     */
    public $allowEmptyValue;

    /**
     * @var bool
     */
    public $allowReserved;

    /**
     * @var \Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface
     */
    public $schema;

    /**
     * @var mixed
     */
    public $example;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\ExampleInterface>
     */
    public $examples;
}
