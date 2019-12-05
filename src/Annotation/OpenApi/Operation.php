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
 */
final class Operation
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
     * @var array<Sunrise\Http\Router\Annotation\OpenApi\Parameter>
     */
    public $parameters = [];

    /**
     * @var Sunrise\Http\Router\Annotation\OpenApi\RequestBody
     */
    public $requestBody;

    /**
     * @var array<Sunrise\Http\Router\Annotation\OpenApi\Response>
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

        foreach ($this->responses as $value) {
            $result['responses'][$value->code] = $value->toArray();
        }

        return $result;
    }
}
