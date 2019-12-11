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
use Sunrise\Http\Router\Annotation\OpenApi\Parameter as ParameterObject;
use Sunrise\Http\Router\Annotation\OpenApi\ParameterInterface;

/**
 * @Annotation
 *
 * @Target({"ANNOTATION"})
 */
final class Parameter extends AbstractReference implements ParameterInterface
{

    /**
     * {@inheritDoc}
     */
    public function getAnnotationName() : string
    {
        return ParameterObject::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getComponentName() : string
    {
        return 'parameters';
    }
}
