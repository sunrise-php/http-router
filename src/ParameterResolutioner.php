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

use Generator;
use ReflectionMethod;
use ReflectionParameter;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolver\ParameterResolverInterface;

use function sprintf;

/**
 * ParameterResolutioner
 *
 * @since 3.0.0
 */
final class ParameterResolutioner implements ParameterResolutionerInterface
{

    /**
     * @var mixed
     */
    private mixed $context = null;

    /**
     * @var list<ParameterResolverInterface>
     */
    private array $resolvers = [];

    /**
     * @inheritDoc
     */
    public function withContext(mixed $context): static
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withPriorityResolver(ParameterResolverInterface ...$resolvers): static
    {
        $clone = clone $this;
        $clone->resolvers = [];

        foreach ($resolvers as $resolver) {
            $clone->resolvers[] = $resolver;
        }

        foreach ($this->resolvers as $resolver) {
            $clone->resolvers[] = $resolver;
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function addResolver(ParameterResolverInterface ...$resolvers): void
    {
        foreach ($resolvers as $resolver) {
            $this->resolvers[] = $resolver;
        }
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If one of the parameters cannot be resolved to an argument(s).
     */
    public function resolveParameters(ReflectionParameter ...$parameters): Generator
    {
        foreach ($parameters as $parameter) {
            yield from $this->resolveParameter($parameter);
        }
    }

    /**
     * Tries to resolve the given parameter to an argument(s)
     *
     * @param ReflectionParameter $parameter
     *
     * @return Generator<mixed>
     *
     * @throws LogicException If the parameter cannot be resolved to an argument(s).
     */
    private function resolveParameter(ReflectionParameter $parameter): Generator
    {
        foreach ($this->resolvers as $resolver) {
            $arguments = $resolver->resolveParameter($parameter, $this->context);
            if ($arguments->valid()) {
                return yield from $arguments;
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
           return yield $parameter->getDefaultValue();
        }

        throw new LogicException(sprintf(
            'Unable to resolve the parameter {%s}.',
            self::stringifyParameter($parameter)
        ));
    }

    /**
     * Stringifies the given parameter
     *
     * @param ReflectionParameter $parameter
     *
     * @return non-empty-string
     */
    public static function stringifyParameter(ReflectionParameter $parameter): string
    {
        if ($parameter->getDeclaringFunction() instanceof ReflectionMethod) {
            return sprintf(
                '%s::%s($%s[%d])',
                $parameter->getDeclaringFunction()->getDeclaringClass()->getName(),
                $parameter->getDeclaringFunction()->getName(),
                $parameter->getName(),
                $parameter->getPosition()
            );
        }

        return sprintf(
            '%s($%s[%d])',
            $parameter->getDeclaringFunction()->getName(),
            $parameter->getName(),
            $parameter->getPosition()
        );
    }
}
