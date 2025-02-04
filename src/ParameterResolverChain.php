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
use InvalidArgumentException;
use LogicException;
use ReflectionMethod;
use ReflectionParameter;

use function sprintf;
use function usort;

/**
 * @since 3.0.0
 */
final class ParameterResolverChain implements ParameterResolverChainInterface
{
    private mixed $context = null;

    private bool $isSorted = false;

    public function __construct(
        /** @var array<array-key, ParameterResolverInterface> */
        private array $resolvers = [],
    ) {
    }

    public function withContext(mixed $context): static
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    public function withResolver(ParameterResolverInterface ...$resolvers): static
    {
        $clone = clone $this;
        $clone->isSorted = false;
        foreach ($resolvers as $resolver) {
            $clone->resolvers[] = $resolver;
        }

        return $clone;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function resolveParameters(ReflectionParameter ...$parameters): Generator
    {
        $this->isSorted or $this->sortResolvers();
        foreach ($parameters as $parameter) {
            yield from $this->resolveParameter($parameter);
        }
    }

    /**
     * @return Generator<int, mixed>
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    private function resolveParameter(ReflectionParameter $parameter): Generator
    {
        foreach ($this->resolvers as $resolver) {
            $arguments = $resolver->resolveParameter($parameter, $this->context);
            if ($arguments->valid()) {
                return yield from $arguments;
            }
        }

        throw new LogicException(sprintf(
            'The parameter "%s" is not supported and cannot be resolved.',
            self::stringifyParameter($parameter),
        ));
    }

    private function sortResolvers(): void
    {
        $this->isSorted = usort($this->resolvers, static fn(
            ParameterResolverInterface $a,
            ParameterResolverInterface $b,
        ): int => $b->getWeight() <=> $a->getWeight());
    }

    public static function stringifyParameter(ReflectionParameter $parameter): string
    {
        $function = $parameter->getDeclaringFunction();
        $position = $parameter->getPosition();

        if ($function instanceof ReflectionMethod) {
            return sprintf('%s::%s($%s[%d])', $function->class, $function->name, $parameter->name, $position);
        }

        return sprintf('%s($%s[%d])', $function->name, $parameter->name, $position);
    }
}
