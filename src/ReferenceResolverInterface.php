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
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Exception\UnresolvableReferenceException;

/**
 * ReferenceResolverInterface
 *
 * @since 2.10.0
 */
interface ReferenceResolverInterface
{

    /**
     * Gets the reference resolver container
     *
     * @return ContainerInterface|null
     */
    public function getContainer() : ?ContainerInterface;

    /**
     * Sets the given container to the reference resolver
     *
     * @param ContainerInterface|null $container
     *
     * @return void
     */
    public function setContainer(?ContainerInterface $container) : void;

    /**
     * Resolves the given reference to a request handler
     *
     * @param mixed $reference
     *
     * @return RequestHandlerInterface
     *
     * @throws UnresolvableReferenceException
     *         If the given reference cannot be resolved to a request handler.
     */
    public function toRequestHandler($reference) : RequestHandlerInterface;

    /**
     * Resolves the given reference to a middleware
     *
     * @param mixed $reference
     *
     * @return MiddlewareInterface
     *
     * @throws UnresolvableReferenceException
     *         If the given reference cannot be resolved to a middleware.
     */
    public function toMiddleware($reference) : MiddlewareInterface;
}
