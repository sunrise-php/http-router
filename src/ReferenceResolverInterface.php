<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
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
use Sunrise\Http\Router\Exception\ReferenceResolvingException;

/**
 * ReferenceResolverInterface
 *
 * @since 2.10.0
 */
interface ReferenceResolverInterface
{

    /**
     * Sets the given container to the resolver
     *
     * @param ContainerInterface|null $container
     *
     * @return void
     */
    public function setContainer(?ContainerInterface $container): void;

    /**
     * Resolves the given reference to a request handler
     *
     * @param mixed $reference
     *
     * @return RequestHandlerInterface
     *
     * @throws ReferenceResolvingException
     *         If the reference cannot be resolved to a request handler.
     */
    public function resolveRequestHandler($reference): RequestHandlerInterface;

    /**
     * Resolves the given reference to a middleware
     *
     * @param mixed $reference
     *
     * @return MiddlewareInterface
     *
     * @throws ReferenceResolvingException
     *         If the reference cannot be resolved to a middleware.
     */
    public function resolveMiddleware($reference): MiddlewareInterface;

    /**
     * Resolves the given references to middlewares
     *
     * @param array<array-key, mixed> $references
     *
     * @return list<MiddlewareInterface>
     *
     * @throws ReferenceResolvingException
     *         If one of the references cannot be resolved to a middleware.
     *
     * @since 3.0.0
     */
    public function resolveMiddlewares(array $references): array;
}
