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
final class Header
{

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
    public $deprecated = false;

    /**
     * @var bool
     */
    public $required = false;

    /**
     * @var bool
     */
    public $allowEmptyValue = false;

    /**
     * @var Sunrise\Http\Router\Annotation\OpenApi\Schema
     */
    public $schema;

    /**
     * @return array
     */
    public function toArray() : array
    {
        $result = [
            'description' => $this->description,
            'deprecated' => $this->deprecated,
            'required' => $this->required,
            'allowEmptyValue' => $this->allowEmptyValue,
        ];

        if (isset($this->schema)) {
            $result['schema'] = $this->schema->toArray();
        }

        return $result;
    }
}
