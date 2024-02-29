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
use LogicException;
use ReflectionMethod;
use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolver\ParameterResolverInterface;

use function sprintf;

/**
 * @since 3.0.0
 */
final class ParameterResolver
{
    private mixed $context;

    public function __construct(
        /** @var ParameterResolverInterface[] */
        private array $resolvers,
    ) {
    }

    public function withContext(mixed $context): self
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    public function withPriorityResolver(ParameterResolverInterface ...$resolvers): self
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
     * @throws LogicException If one of the parameters couldn't be resolved to an argument(s).
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
     * @return Generator<int, mixed>
     *
     * @throws LogicException If the parameter couldn't be resolved to an argument(s).
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
            'The parameter {%s} cannot be resolved.',
            self::stringifyParameter($parameter)
        ));
    }

    /**
     * Stringifies the given parameter
     *
     * @param ReflectionParameter $parameter
     *
     * @return string
     */
    public static function stringifyParameter(ReflectionParameter $parameter): string
    {
        if ($parameter->getDeclaringFunction() instanceof ReflectionMethod) {
            return sprintf(
                '%s::%s($%s[%d])',
                $parameter->getDeclaringFunction()->getDeclaringClass()->getName(),
                $parameter->getDeclaringFunction()->getName(),
                $parameter->getName(),
                $parameter->getPosition(),
            );
        }

        return sprintf(
            '%s($%s[%d])',
            $parameter->getDeclaringFunction()->getName(),
            $parameter->getName(),
            $parameter->getPosition(),
        );
    }
}
