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
use ReflectionClass;

use function class_exists;
use function sprintf;

/**
 * @since 3.0.0
 */
final class ClassResolver
{
    /**
     * @var array<class-string, object>
     */
    private array $resolvedClasses = [];

    public function __construct(
        private readonly ParameterResolver $parameterResolver,
    ) {
    }

    /**
     * @param class-string<T> $fqn
     *
     * @return T
     *
     * @template T of object
     *
     * @throws InvalidArgumentException If the class cannot be resolved.
     */
    public function resolveClass(string $fqn): object
    {
        if (isset($this->resolvedClasses[$fqn])) {
            /** @var T */
            return $this->resolvedClasses[$fqn];
        }

        if (!class_exists($fqn)) {
            throw new InvalidArgumentException(sprintf('The class %s does not exist.', $fqn));
        }

        /** @var ReflectionClass<T> $reflection */
        $reflection = new ReflectionClass($fqn);

        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException(sprintf('The class %s is not instantiable.', $fqn));
        }

        $arguments = $this->parameterResolver->resolveParameters(
            ...($reflection->getConstructor()?->getParameters() ?? [])
        );

        return $this->resolvedClasses[$fqn] = $reflection->newInstance(...$arguments);
    }
}
