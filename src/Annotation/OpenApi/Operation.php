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
 * @Target({"CLASS"})
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#operation-object
 */
final class Operation extends AbstractAnnotation
{

    /**
     * @var array<string>
     */
    public $tags;

    /**
     * @var string
     */
    public $summary;

    /**
     * @var string
     */
    public $description;

    /**
     * @var \Sunrise\Http\Router\Annotation\OpenApi\ExternalDocumentationInterface
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-operationexternaldocs
     */
    public $externalDocs;

    /**
     * @var bool
     */
    public $deprecated;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\ParameterInterface>
     */
    public $parameters;

    /**
     * @var \Sunrise\Http\Router\Annotation\OpenApi\RequestBodyInterface
     */
    public $requestBody;

    /**
     * @Required
     *
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\ResponseInterface>
     */
    public $responses;
}
