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
final class Example
{

    /**
     * @var string
     */
    public $summary = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $value;

    /**
     * @var mixed
     */
    public $extendedValue;

    /**
     * @var string
     */
    public $externalValue;

    /**
     * @return array
     */
    public function toArray() : array
    {
        $result = [
            'summary' => $this->summary,
            'description' => $this->description,
            'value' => $this->extendedValue ?? $this->value,
        ];

        if (isset($this->externalValue)) {
            $result['externalValue'] = $this->externalValue;
        }

        return $result;
    }
}
