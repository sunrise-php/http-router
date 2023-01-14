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
use Psr\Container\ContainerInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * ParameterResolver
 *
 * @since 3.0.0
 */
final class ParameterResolver implements ParameterResolverInterface
{

    /**
     * Known parameter names
     *
     * @var array<non-empty-string, mixed>
     */
    private array $names = [];

    /**
     * Known types
     *
     * @var array<non-empty-string, mixed>
     */
    private array $types = [];

    /**
     * The resolver's container
     *
     * @var ContainerInterface|null
     */
    private ?ContainerInterface $container = null;

    /**
     * {@inheritdoc}
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
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
    public function withName(string $name, $value): ParameterResolverInterface
    {
        $clone = clone $this;
        $clone->setName($name, $value);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withNames(array $names): ParameterResolverInterface
    {
        $clone = clone $this;
        foreach ($names as $name => $value) {
            $clone->setName($name, $value);
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withType(string $type, $value): ParameterResolverInterface
    {
        $clone = clone $this;
        $clone->setType($type, $value);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withTypes(array $types): ParameterResolverInterface
    {
        $clone = clone $this;
        foreach ($types as $type => $value) {
            $clone->setType($type, $value);
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveParameters(ReflectionParameter ...$parameters): array
    {
        $arguments = [];
        foreach ($parameters as $parameter) {
            $arguments[] = $this->resolveParameter($parameter);
        }

        return $arguments;
    }

    /**
     * Sets a new known name
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    private function setName(string $name, $value): void
    {
        $this->names[$name] = $value;
    }

    /**
     * Sets a new known type
     *
     * @param string $type
     * @param mixed $value
     *
     * @return void
     *
     * @throws LogicException
     *         If the value isn't an instance of the type.
     */
    private function setType(string $type, $value): void
    {
        if (!($value instanceof $type)) {
            throw new LogicException();
        }

        $this->types[$type] = $value;
    }

    /**
     * Resolves the given parameter
     *
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     *
     * @throws LogicException
     *         If the parameter cannot be resolved.
     */
    private function resolveParameter(ReflectionParameter $parameter)
    {
        if (!$parameter->hasType()) {
            return $this->resolveUntypedParameter($parameter);
        }

        $type = $parameter->getType();
        if (!($type instanceof ReflectionNamedType)) {
            throw new LogicException();
        }

        return !$type->isBuiltin() ?
            $this->resolveTypedParameterWithNonBuiltinType($type, $parameter) :
            $this->resolveTypedParameterWithBuiltinType($type, $parameter);
    }

    /**
     * Resolves the given untyped parameter
     *
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     *
     * @throws LogicException
     *         If the parameter cannot be resolved.
     */
    private function resolveUntypedParameter(ReflectionParameter $parameter)
    {
        if (array_key_exists($parameter->getName(), $this->names)) {
            return $this->names[$parameter->getName()];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new LogicException();
    }

    /**
     * Resolves the given typed parameter with non-built-in type
     *
     * @param ReflectionNamedType $type
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     *
     * @throws LogicException
     *         If the parameter cannot be resolved.
     */
    private function resolveTypedParameterWithNonBuiltinType(ReflectionNamedType $type, ReflectionParameter $parameter)
    {
        if (isset($this->types[$parameter->getName()])) {
            return $this->types[$parameter->getName()];
        }

        if (isset($this->container) && $this->container->has($type->getName())) {
            return $this->container->get($type->getName());
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new LogicException();
    }

    /**
     * Resolves the given typed parameter with built-in type
     *
     * @param ReflectionNamedType $type
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     *
     * @throws LogicException
     *         If the parameter cannot be resolved.
     */
    private function resolveTypedParameterWithBuiltinType(ReflectionNamedType $type, ReflectionParameter $parameter)
    {
        if (!array_key_exists($parameter->getName(), $this->names)) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new LogicException();
        }

        switch ($type->getName()) {
            case 'bool':
                break;
            case 'int':
                break;
            case 'float':
                break;
            case 'string':
                break;
        }

        throw new LogicException();
    }
}
