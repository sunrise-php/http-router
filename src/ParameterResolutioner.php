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
use ReflectionNamedType;
use ReflectionParameter;
use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\Exception\ParameterResolvingException;

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
     * Known types
     *
     * @var array<string, object>
     */
    private array $types = [];

    /**
     * The resolutioner's resolvers
     *
     * @var list<ParameterResolverInterface>
     */
    private array $resolvers = [];

    /**
     * The resolutioner's container
     *
     * @var ContainerInterface|null
     */
    private ?ContainerInterface $container = null;

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
    public function withType(string $type, object $value): ParameterResolutionerInterface
    {
        $clone = clone $this;
        $clone->types[$type] = $value;

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
    public function setContainer(?ContainerInterface $container): void
    {
        $this->container = $container;
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
     *         The ready-to-pass argument.
     *
     * @throws ParameterResolvingException
     *         If the parameter cannot be resolved to an argument.
     */
    private function resolveParameter(ReflectionParameter $parameter)
    {
        $type = $parameter->getType();

        if (($type instanceof ReflectionNamedType) && !$type->isBuiltin()) {
            if (isset($this->types[$type->getName()])) {
                return $this->types[$type->getName()];
            }
        }

        foreach ($this->resolvers as $resolver) {
            if ($resolver->supportsParameter($parameter, $this->context)) {
                return $resolver->resolveParameter($parameter, $this->context);
            }
        }

        if (($type instanceof ReflectionNamedType) && !$type->isBuiltin()) {
            if (isset($this->container) && $this->container->has($type->getName())) {
                return $this->container->get($type->getName());
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new ParameterResolvingException(sprintf(
            'Unexpected parameter {%s($%s[%d])}',
            $parameter->getDeclaringFunction()->getName(),
            $parameter->getName(),
            $parameter->getPosition()
        ));
    }
}
