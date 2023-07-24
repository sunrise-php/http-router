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

use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Exception\LogicException;
use ReflectionNamedType;
use ReflectionParameter;

use function sprintf;

/**
 * TypeParameterResolver
 *
 * @since 3.0.0
 */
final class TypeParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param string $type
     * @param object $value
     *
     * @throws LogicException
     *         If the value isn't an instance of the type.
     */
    public function __construct(private string $type, private object $value)
    {
        if (! $this->value instanceof $this->type) {
            throw new LogicException(sprintf(
                'The %1$s value must be an instance of %2$s.',
                $this->value::class,
                $this->type,
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function supportsParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): bool
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType) {
            return false;
        }

        return $type->getName() === $this->type;
    }

    /**
     * @inheritDoc
     */
    public function resolveParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): mixed
    {
        return $this->value;
    }
}
