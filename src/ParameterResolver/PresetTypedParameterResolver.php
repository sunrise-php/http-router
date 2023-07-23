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

namespace Sunrise\Http\Router\ParameterResolver;

use Psr\Http\Message\RequestInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * PresetTypedParameterResolver
 *
 * @template T of object
 *
 * @since 3.0.0
 */
final class PresetTypedParameterResolver implements ParameterResolverInterface
{

    /**
     * @param class-string<T> $type
     * @param T $value
     *
     * @template T of object
     */
    public function __construct(private string $type, private object $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supportsParameter(ReflectionParameter $parameter, RequestInterface $request): bool
    {
        $type = $parameter->getType();

        if (!($type instanceof ReflectionNamedType)) {
            return false;
        }

        if ($type->isBuiltin()) {
            return false;
        }

        if (!($type->getName() === $this->type)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveParameter(ReflectionParameter $parameter, RequestInterface $request): mixed
    {
        return $this->value;
    }
}
