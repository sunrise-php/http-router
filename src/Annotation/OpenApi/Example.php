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
 * Import classes
 */
use Sunrise\Http\Router\OpenApi\AbstractAnnotation;
use Sunrise\Http\Router\OpenApi\ComponentObjectInterface;

/**
 * Import functions
 */
use function spl_object_hash;

/**
 * @Annotation
 *
 * @Target({"ALL"})
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#example-object
 */
final class Example extends AbstractAnnotation implements ExampleInterface, ComponentObjectInterface
{

    /**
     * {@inheritDoc}
     */
    protected const IGNORE_FIELDS = ['refName'];

    /**
     * {@inheritDoc}
     */
    protected const FIELD_ALIASES = ['anyValue' => 'value'];

    /**
     * @var string
     */
    public $refName;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-examplesummary
     */
    public $summary;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-exampledescription
     */
    public $description;

    /**
     * This property cannot be named as "value" because the name is reserved by the Doctrine Annotations Library.
     *
     * @see https://github.com/doctrine/annotations/blob/b4fde48ffe28bf766077f8d41b5d23049a0687a8/lib/Doctrine/Common/Annotations/DocParser.php#L786
     *
     * @var mixed
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-examplevalue
     */
    public $anyValue;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-exampleexternalvalue
     */
    public $externalValue;

    /**
     * {@inheritDoc}
     */
    public function getComponentName() : string
    {
        return 'examples';
    }

    /**
     * {@inheritDoc}
     */
    public function getReferenceName() : string
    {
        return $this->refName ?? spl_object_hash($this);
    }
}
