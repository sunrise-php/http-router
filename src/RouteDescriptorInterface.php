<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Psr\Http\Server\MiddlewareInterface;

/**
 * RouteDescriptorInterface
 *
 * @since 2.6.0
 */
interface RouteDescriptorInterface
{

    /**
     * Gets a route name
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Gets a route host
     *
     * @return null|string
     */
    public function getHost() : ?string;

    /**
     * Gets a route path
     *
     * @return string
     */
    public function getPath() : string;

    /**
     * Gets a route methods
     *
     * @return string[]
     */
    public function getMethods() : array;

    /**
     * Gets a route middlewares
     *
     * @return string[]
     */
    public function getMiddlewares() : array;

    /**
     * Gets a route attributes
     *
     * @return array
     */
    public function getAttributes() : array;

    /**
     * Gets a route summary
     *
     * @return string
     */
    public function getSummary() : string;

    /**
     * Gets a route description
     *
     * @return string
     */
    public function getDescription() : string;

    /**
     * Gets a route tags
     *
     * @return string[]
     */
    public function getTags() : array;

    /**
     * Gets a route priority
     *
     * @return int
     */
    public function getPriority() : int;
}
