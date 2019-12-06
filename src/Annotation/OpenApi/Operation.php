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
    public $tags = [];

    /**
     * @var string
     */
    public $summary = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var bool
     */
    public $deprecated = false;

    /**
     * @var array<Sunrise\Http\Router\Annotation\OpenApi\ParameterInterface>
     */
    public $parameters = [];

    /**
     * @var Sunrise\Http\Router\Annotation\OpenApi\RequestBodyInterface
     */
    public $requestBody;

    /**
     * @Required
     *
     * @var array<Sunrise\Http\Router\Annotation\OpenApi\ResponseInterface>
     */
    public $responses = [];

    /**
     * @return array
     */
    public function toArray() : array
    {
        $result = [
            'tags' => $this->tags,
            'summary' => $this->summary,
            'description' => $this->description,
            'deprecated' => $this->deprecated,
        ];

        foreach ($this->parameters as $value) {
            $result['parameters'][] = $value->toArray();
        }

        if (isset($this->requestBody)) {
            $result['requestBody'] = $this->requestBody->toArray();
        }

        foreach ($this->responses as $key => $value) {
            $result['responses'][$key] = $value->toArray();
        }

        return $result;
    }
}
