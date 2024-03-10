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
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolver\ParameterResolverInterface;

use function array_unshift;
use function sprintf;

/**
 * @since 3.0.0
 */
final class ParameterResolver
{
    private ?ServerRequestInterface $request = null;

    public function __construct(
        /** @var ParameterResolverInterface[] */
        private array $resolvers,
    ) {
    }

    public function withRequest(ServerRequestInterface $request): self
    {
        $clone = clone $this;
        $clone->request = $request;

        return $clone;
    }

    public function withPriorityResolver(ParameterResolverInterface ...$resolvers): self
    {
        $clone = clone $this;

        array_unshift($clone->resolvers, ...$resolvers);

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
     * @return Generator<int, mixed>
     *
     * @throws LogicException If the parameter couldn't be resolved to an argument(s).
     */
    private function resolveParameter(ReflectionParameter $parameter): Generator
    {
        foreach ($this->resolvers as $resolver) {
            $arguments = $resolver->resolveParameter($parameter, $this->request);
            if ($arguments->valid()) {
                return yield from $arguments;
            }
        }

        throw new LogicException(sprintf(
            'The parameter %s could not be resolved because it is not supported.',
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
