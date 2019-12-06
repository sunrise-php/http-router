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
final class RequestBodyReference extends AbstractReference implements RequestBodyInterface
{

    /**
     * {@inheritDoc}
     */
    public function getAnnotationName() : string
    {
        return RequestBody::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getComponentName() : string
    {
        return 'requestBodies';
    }
}
