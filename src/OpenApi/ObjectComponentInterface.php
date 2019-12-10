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
 * ObjectComponentInterface
 */
interface ObjectComponentInterface
{

    /**
     * Gets access key for a component object
     *
     * @return string
     */
    public function getAccessKey() : string;

    /**
     * Gets a component name
     *
     * @return string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#fixed-fields-6
     */
    public function getComponentName() : string;

    /**
     * Gets a component object path
     *
     * @return string
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#reference-object-example
     */
    public function getComponentObjectPath() : string;
}
