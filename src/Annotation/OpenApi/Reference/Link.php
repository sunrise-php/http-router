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
use Sunrise\Http\Router\Annotation\OpenApi\Link as LinkObject;
use Sunrise\Http\Router\Annotation\OpenApi\LinkInterface;

/**
 * @Annotation
 *
 * @Target({"ANNOTATION"})
 */
final class Link extends AbstractReference implements LinkInterface
{

    /**
     * {@inheritDoc}
     */
    public function getAnnotationName() : string
    {
        return LinkObject::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getComponentName() : string
    {
        return 'links';
    }
}
