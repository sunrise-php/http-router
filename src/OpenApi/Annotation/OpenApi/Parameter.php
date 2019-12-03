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
 * @Target({"ANNOTATION"})
 */
final class Parameter
{

    /**
     * @Required
     *
     * @Enum({"cookie", "header", "query"})
     *
     * @var string
     */
    public $in;

    /**
     * @Required
     *
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var bool
     */
    public $required = false;

    /**
     * @var bool
     */
    public $allowEmptyValue = false;

    /**
     * @var bool
     */
    public $deprecated = false;

    /**
     * @return array
     */
    public function toArray() : array
    {
        return [
            'in' => $this->in,
            'name' => $this->name,
            'description' => $this->description,
            'required' => $this->required,
            'allowEmptyValue' => $this->allowEmptyValue,
            'deprecated' => $this->deprecated,
        ];
    }
}
