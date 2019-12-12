<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\OpenApi;

/**
 * ComponentObjectInterface
 */
interface ComponentObjectInterface extends ObjectInterface
{

    /**
     * Gets a component name
     *
     * @return string
     */
    public function getComponentName() : string;

    /**
     * Gets a reference name
     *
     * @return string
     */
    public function getReferenceName() : string;
}
