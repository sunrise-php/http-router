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

use Generator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * @since 3.0.0
 */
final class DependencyInjectionParameterResolver implements ParameterResolverInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * @inheritDoc
     *
     * @throws ContainerExceptionInterface
     */
    public function resolveParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): Generator
    {
        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return;
        }

        $typeName = $type->getName();
        if ($this->container->has($typeName)) {
            yield $this->container->get($typeName);
        }
    }
}
