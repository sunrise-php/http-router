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
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#request-body-object
 */
final class RequestBody extends AbstractAnnotation implements RequestBodyInterface
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
     * @Required
     *
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\MediaTypeInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#media-types
     */
    public $content = [];

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        $result = [
            'description' => $this->description,
            'required' => $this->required,
        ];

        foreach ($this->content as $key => $value) {
            $result['content'][$key] = $value->toArray();
        }

        return $result;
    }
}
