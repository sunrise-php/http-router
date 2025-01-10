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

namespace Sunrise\Http\Router;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

use function class_exists;
use function sprintf;

/**
 * @since 3.0.0
 */
final class ClassResolver implements ClassResolverInterface
{
    /**
     * @var array<class-string, object>
     */
    private array $resolvedClasses = [];

    public function __construct(
        private readonly ParameterResolverChainInterface $parameterResolverChain,
        private readonly ?ContainerInterface $container,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @param class-string<T> $className
     *
     * @return T
     *
     * @template T of object
     *
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function resolveClass(string $className): object
    {
        if ($this->container?->has($className) === true) {
            /** @var T */
            return $this->container->get($className);
        }

        if (isset($this->resolvedClasses[$className])) {
            /** @var T */
            return $this->resolvedClasses[$className];
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('The class %s does not exist.', $className));
        }

        /** @var ReflectionClass<T> $classReflection */
        $classReflection = new ReflectionClass($className);

        if (!$classReflection->isInstantiable()) {
            throw new InvalidArgumentException(sprintf('The class %s is not instantiable.', $className));
        }

        $this->resolvedClasses[$className] = $classReflection->newInstance(
            ...$this->parameterResolverChain->resolveParameters(
                ...($classReflection->getConstructor()?->getParameters() ?? [])
            )
        );

        return $this->resolvedClasses[$className];
    }
}
