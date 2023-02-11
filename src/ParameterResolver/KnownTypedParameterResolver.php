<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\ParameterResolver;

/**
 * Import classes
 */
use Sunrise\Http\Router\ParameterResolverInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * KnownTypedParameterResolver
 *
 * @template T as object
 *
 * @since 3.0.0
 */
final class KnownTypedParameterResolver implements ParameterResolverInterface
{

    /**
     * @var class-string<T>
     */
    private string $type;

    /**
     * @var T
     */
    private object $value;

    /**
     * @param class-string<T> $type
     * @param T $value
     */
    public function __construct(string $type, object $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsParameter(ReflectionParameter $parameter, $context): bool
    {
        if (!($parameter->getType() instanceof ReflectionNamedType)) {
            return false;
        }

        if ($parameter->getType()->isBuiltin()) {
            return false;
        }

        if (!($parameter->getType()->getName() === $this->type)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveParameter(ReflectionParameter $parameter, $context)
    {
        return $this->value;
    }
}
