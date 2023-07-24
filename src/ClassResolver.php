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

use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\Exception\LogicException;
use ReflectionClass;

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
     */
    public function resolveClass(string $className): object
    {
        if (isset($this->resolvedClasses[$className])) {
            return $this->resolvedClasses[$className];
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf(
                'Class %s does not exist',
                $className
            ));
        }

        $reflection = new ReflectionClass($className);
        if (!$reflection->isInstantiable()) {
            throw new LogicException(sprintf(
                'Class %s cannot be initialized',
                $className
            ));
        }

        $arguments = [];
        $constructor = $reflection->getConstructor();
        if (isset($constructor) && $constructor->getNumberOfParameters() > 0) {
            $arguments = $this->parameterResolutioner->resolveParameters(
                ...$constructor->getParameters()
            );
        }

        /** @var T */
        $this->resolvedClasses[$className] = $reflection->newInstance(...$arguments);

        return $this->resolvedClasses[$className];
    }
}
