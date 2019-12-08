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
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#encoding-object
 */
final class Encoding extends AbstractAnnotation implements EncodingInterface
{

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-encodingcontenttype
     */
    public $contentType;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\HeaderInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-encodingheaders
     */
    public $headers = [];

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-encodingstyle
     */
    public $style;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-encodingexplode
     */
    public $explode;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-encodingallowreserved
     */
    public $allowReserved = false;

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        $result = [
            'allowReserved' => $this->allowReserved,
        ];

        if (isset($this->contentType)) {
            $result['contentType'] = $this->contentType;
        }

        foreach ($this->headers as $key => $value) {
            $result['headers'][$key] = $value->toArray();
        }

        if (isset($this->style)) {
            $result['style'] = $this->style;
        }

        if (isset($this->explode)) {
            $result['explode'] = $this->explode;
        }

        return $result;
    }
}
