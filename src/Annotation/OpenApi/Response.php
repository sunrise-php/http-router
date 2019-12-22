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
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#response-object
 */
final class Response extends AbstractAnnotation implements ResponseInterface, ComponentObjectInterface
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
     * @Required
     *
     * @var string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-responsedescription
     */
    public $description;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\HeaderInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-responseheaders
     */
    public $headers;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\MediaTypeInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-responsecontent
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#media-types
     */
    public $content;

    /**
     * @var array<\Sunrise\Http\Router\Annotation\OpenApi\LinkInterface>
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-responselinks
     */
    public $links;

    /**
     * {@inheritDoc}
     */
    public function getComponentName() : string
    {
        return 'responses';
    }

    /**
     * {@inheritDoc}
     */
    public function getReferenceName() : string
    {
        return $this->refName ?? spl_object_hash($this);
    }
}
