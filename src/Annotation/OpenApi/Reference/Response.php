<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Annotation\OpenApi\Reference;

/**
 * Import classes
 */
use Sunrise\Http\Router\Annotation\OpenApi\Response as ResponseObject;
use Sunrise\Http\Router\Annotation\OpenApi\ResponseInterface;

/**
 * @Annotation
 *
 * @Target({"ANNOTATION"})
 */
final class Response extends AbstractReference implements ResponseInterface
{

    /**
     * {@inheritDoc}
     */
    public function getAnnotationName() : string
    {
        return ResponseObject::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getComponentName() : string
    {
        return 'responses';
    }
}
