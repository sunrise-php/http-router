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

use ReflectionClass;
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\Exception\LogicException;

use function class_exists;
use function sprintf;

/**
 * ClassResolver
 *
 * @since 3.0.0
 *
 * @template T of object
 *
 * @implements ClassResolverInterface<T>
 */
final class ClassResolver implements ClassResolverInterface
{

    /**
     * @var array<class-string<T>, T>
     */
    private array $resolvedClasses = [];

    /**
     * @var ParameterResolutionerInterface
     */
    private ParameterResolutionerInterface $parameterResolutioner;

    /**
     * Constructor of the class
     *
     * @param ParameterResolutionerInterface $parameterResolutioner
     */
    public function __construct(ParameterResolutionerInterface $parameterResolutioner)
    {
        $this->parameterResolutioner = $parameterResolutioner;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException If the class doesn't exist.
     * @throws LogicException If the class cannot be resolved.
     */
    public function resolveClass(string $fqn): object
    {
        if (isset($this->resolvedClasses[$fqn])) {
            return $this->resolvedClasses[$fqn];
        }

        if (!class_exists($fqn)) {
            throw new InvalidArgumentException(sprintf('The class %s does not exist', $fqn));
        }

        $class = new ReflectionClass($fqn);
        if (!$class->isInstantiable()) {
            throw new LogicException(sprintf('The class %s cannot be initialized', $fqn));
        }

        $arguments = [];
        $constructor = $class->getConstructor();
        if (isset($constructor) && $constructor->getNumberOfParameters() > 0) {
            $arguments = $this->parameterResolutioner->resolveParameters(
                ...$constructor->getParameters()
            );
        }

        /** @var T $instance */
        $instance = new $class(...$arguments);

        $this->resolvedClasses[$fqn] = $instance;

        return $this->resolvedClasses[$fqn];
    }
}
