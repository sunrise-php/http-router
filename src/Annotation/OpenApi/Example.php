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
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#example-object
 */
final class Example extends AbstractAnnotation implements ExampleInterface
{

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-examplesummary
     */
    public $summary = '';

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-exampledescription
     */
    public $description = '';

    /**
     * @var mixed
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-examplevalue
     */
    public $value;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-exampleexternalvalue
     */
    public $externalValue;

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        $result = [
            'summary' => $this->summary,
            'description' => $this->description,
        ];

        // The `value` field and `externalValue` field are mutually exclusive.
        if (isset($this->value)) {
            $result['value'] = $this->value;
        } elseif (isset($this->externalValue)) {
            $result['externalValue'] = $this->externalValue;
        }

        return $result;
    }
}
