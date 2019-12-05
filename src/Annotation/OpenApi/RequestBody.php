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
 */
final class RequestBody
{

    /**
     * @var string
     */
    public $description = '';

    /**
     * @Required
     *
     * @var array<Sunrise\Http\Router\Annotation\OpenApi\MediaType>
     */
    public $content = [];

    /**
     * @var bool
     */
    public $required = false;

    /**
     * @return array
     */
    public function toArray() : array
    {
        $result = [
            'description' => $this->description,
            'required' => $this->required,
        ];

        foreach ($this->content as $value) {
            $result['content'][$value->mediaType] = $value->toArray();
        }

        return $result;
    }
}
