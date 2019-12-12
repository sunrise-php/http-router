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
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#request-body-object
 */
final class RequestBody extends AbstractAnnotation implements RequestBodyInterface, ComponentObjectInterface
{

    /**
     * {@inheritDoc}
     */
    protected const IGNORE_FIELDS = ['refName'];

    /**
     * @var string
     */
    public $refName;

    /**
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-requestbodydescription
     */
    public $description;

    /**
     * @Required
     *
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\MediaTypeInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-requestbodycontent
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#media-types
     */
    public $content;

    /**
     * @var bool
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-requestbodyrequired
     */
    public $required;

    /**
     * {@inheritDoc}
     */
    public function getComponentName() : string
    {
        return 'requestBodies';
    }

    /**
     * {@inheritDoc}
     */
    public function getReferenceName() : string
    {
        return $this->refName ?? spl_object_hash($this);
    }
}
