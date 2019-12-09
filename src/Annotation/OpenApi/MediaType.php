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
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#media-type-object
 */
final class MediaType extends AbstractAnnotation implements MediaTypeInterface
{

    /**
     * @var \Sunrise\Http\Router\Annotation\OpenApi\SchemaInterface
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-mediatypeschema
     */
    public $schema;

    /**
     * @var mixed
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-mediatypeexample
     */
    public $example;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\ExampleInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-mediatypeexamples
     */
    public $examples;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\EncodingInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-mediatypeencoding
     */
    public $encoding;
}
