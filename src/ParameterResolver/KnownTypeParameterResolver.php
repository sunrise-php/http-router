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
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\ParameterResolverInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Import classes
 */
use function get_class;
use function sprintf;

/**
 * KnownTypeParameterResolver
 *
 * @since 3.0.0
 */
final class KnownTypeParameterResolver implements ParameterResolverInterface
{

    /**
     * @var class-string
     */
    private string $type;

    /**
     * @var object
     */
    private object $value;

    /**
     * @param class-string $type
     * @param object $value
     *
     * @throws InvalidArgumentException
     *         If the given value is not an instance of the given type.
     */
    public function __construct(string $type, object $value)
    {
        if (!($value instanceof $type)) {
            throw new InvalidArgumentException(sprintf(
                'The known type parameter resolver cannot accept the value "%s" ' .
                'because it is not an instance of the "%s"',
                get_class($value),
                $type
            ));
        }

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
