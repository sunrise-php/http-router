<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\ParameterResolver;

/**
 * Import classes
 */
use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\ParameterResolverInterface;
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
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsParameter(ReflectionParameter $parameter, $context): bool
    {
        if (!($parameter->getType() instanceof ReflectionNamedType)) {
            return false;
        }

        if ($parameter->getType()->isBuiltin()) {
            return false;
        }

        if (!$this->container->has($parameter->getType()->getName())) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveParameter(ReflectionParameter $parameter, $context)
    {
        /** @var ReflectionNamedType */
        $parameterType = $parameter->getType();

        return $this->container->get($parameterType->getName());
    }
}
