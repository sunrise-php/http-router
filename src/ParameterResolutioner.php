<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Sunrise\Http\Router\Exception\ResolvingParameterException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Import functions
 */
use function sprintf;

/**
 * ParameterResolutioner
 *
 * @since 3.0.0
 */
final class ParameterResolutioner implements ParameterResolutionerInterface
{

    /**
     * The current context
     *
     * @var mixed
     */
    private $context = null;

    /**
     * The resolutioner's resolvers
     *
     * @var list<ParameterResolverInterface>
     */
    private array $resolvers = [];

    /**
     * {@inheritdoc}
     */
    public function withContext($context): ParameterResolutionerInterface
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPriorityResolver(ParameterResolverInterface ...$resolvers): ParameterResolutionerInterface
    {
        /** @var list<ParameterResolverInterface> $resolvers */

        foreach ($this->resolvers as $resolver) {
            $resolvers[] = $resolver;
        }

        $clone = clone $this;
        $clone->resolvers = $resolvers;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function addResolver(ParameterResolverInterface ...$resolvers): void
    {
        foreach ($resolvers as $resolver) {
            $this->resolvers[] = $resolver;
        }
    }

    /**
     * {@inheritdoc}
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
    private function resolveParameter(ReflectionParameter $parameter)
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supportsParameter($parameter, $this->context)) {
                return $resolver->resolveParameter($parameter, $this->context);
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
