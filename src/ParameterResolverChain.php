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
use Sunrise\Http\Router\Exception\UnsupportedParameterException;
use Sunrise\Http\Router\ParameterResolver\ParameterResolverInterface;

use function array_unshift;
use function sprintf;
use function usort;

/**
 * @since 3.0.0
 */
final class ParameterResolverChain implements ParameterResolverChainInterface
{
    private mixed $context = null;

    public function __construct(
        /** @var ParameterResolverInterface[] */
        private array $resolvers,
    ) {
        usort($this->resolvers, static fn(
            ParameterResolverInterface $a,
            ParameterResolverInterface $b,
        ): int => $b->getWeight() <=> $a->getWeight());
    }

    public function withContext(mixed $context): static
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    public function withPriorityResolver(ParameterResolverInterface ...$resolvers): static
    {
        $clone = clone $this;

        array_unshift($clone->resolvers, ...$resolvers);

        return $clone;
    }

    /**
     * @inheritDoc
     *
     * @throws UnsupportedParameterException
     */
    public function resolveParameters(ReflectionParameter ...$parameters): Generator
    {
        foreach ($parameters as $parameter) {
            yield from $this->resolveParameter($parameter, $this->context);
        }
    }

    /**
     * @return Generator<int, mixed>
     *
     * @throws UnsupportedParameterException
     */
    private function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        foreach ($this->resolvers as $resolver) {
            $arguments = $resolver->resolveParameter($parameter, $context);
            if ($arguments->valid()) {
                return yield from $arguments;
            }
        }

        throw new UnsupportedParameterException(sprintf(
            'The parameter %s is not supported and cannot be resolved.',
            self::stringifyParameter($parameter),
        ));
    }

    public static function stringifyParameter(ReflectionParameter $parameter): string
    {
        $function = $parameter->getDeclaringFunction();

        if ($function instanceof ReflectionMethod) {
            return sprintf('%s::%s($%s[%d])', $function->getDeclaringClass()->getName(), $function->getName(), $parameter->getName(), $parameter->getPosition());
        }

        return sprintf('%s($%s[%d])', $function->getName(), $parameter->getName(), $parameter->getPosition());
    }
}
