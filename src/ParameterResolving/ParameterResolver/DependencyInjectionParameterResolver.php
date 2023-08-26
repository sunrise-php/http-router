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

namespace Sunrise\Http\Router\ParameterResolving\ParameterResolver;

use Generator;
use Psr\Container\ContainerInterface;
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
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return;
        }

        if ($this->container->has($type->getName())) {
            yield $this->container->get($type->getName());
        }
    }
}
