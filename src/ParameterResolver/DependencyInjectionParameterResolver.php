<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\ParameterResolver;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * DependencyInjectionParameterResolver
 *
 * @since 3.0.0
 */
final class DependencyInjectionParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param ContainerInterface $container
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @inheritDoc
     */
    public function supportsParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): bool
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return false;
        }

        return $this->container->has($type->getName());
    }

    /**
     * @inheritDoc
     */
    public function resolveParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): mixed
    {
        /** @var ReflectionNamedType $type */
        $type = $parameter->getType();

        return $this->container->get($type->getName());
    }
}