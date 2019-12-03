<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\OpenApi\Annotation\OpenApi;

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
     * @var array<Sunrise\Http\Router\OpenApi\Annotation\OpenApi\Parameter>
     */
    public $parameters = [];

    /**
     * @var array<Sunrise\Http\Router\OpenApi\Annotation\OpenApi\Response>
     */
    public $responses = [];

    /**
     * @return array
     */
    public function toArray() : array
    {
        $parameters = [];
        foreach ($this->parameters as $value) {
            $parameters[] = $value->toArray();
        }

        $responses = [];
        foreach ($this->responses as $value) {
            $responses[$value->code] = $value->toArray();
        }

        return [
            'tags' => $this->tags,
            'summary' => $this->summary,
            'description' => $this->description,
            'parameters' => $parameters,
            'responses' => $responses,
        ];
    }
}
