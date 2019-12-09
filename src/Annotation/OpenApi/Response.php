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
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#response-object
 */
final class Response extends AbstractAnnotation implements ResponseInterface
{

    /**
     * @Required
     *
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-responsedescription
     */
    public $description;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\HeaderInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-responseheaders
     */
    public $headers;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\MediaTypeInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-responsecontent
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#media-types
     */
    public $content;
}
