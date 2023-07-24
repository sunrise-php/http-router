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

use Psr\Http\Message\RequestInterface;
use ReflectionMethod;
use ReflectionParameter;
use Sunrise\Http\Router\Exception\ResolvingParameterException;
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
     * @var RequestInterface|null
     */
    private ?RequestInterface $request = null;

    /**
     * @var list<ParameterResolverInterface>
     */
    private array $resolvers = [];

    /**
     * @inheritDoc
     */
    public function withRequest(RequestInterface $request): static
    {
        $clone = clone $this;
        $clone->request = $request;

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
     */
    public function resolveParameters(ReflectionParameter ...$parameters): array
    {
        $arguments = [];
        foreach ($parameters as $parameter) {
            /** @var mixed */
            $arguments[] = $this->resolveParameter($parameter);
        }

        return $arguments;
    }

    /**
     * Tries to resolve the given parameter to an argument
     *
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     *
     * @throws ResolvingParameterException
     *         If the parameter cannot be resolved to an argument.
     */
    private function resolveParameter(ReflectionParameter $parameter): mixed
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supportsParameter($parameter, $this->request)) {
                return $resolver->resolveParameter($parameter, $this->request);
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new ResolvingParameterException(sprintf(
            'Unable to resolve the parameter {%s}',
            $this->stringifyParameter($parameter)
        ));
    }

    /**
     * Stringifies the given parameter
     *
     * @param ReflectionParameter $parameter
     *
     * @return string
     */
    private function stringifyParameter(ReflectionParameter $parameter): string
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
