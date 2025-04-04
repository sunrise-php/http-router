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

use Generator;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolverInterface;

use function is_a;

/**
 * @since 3.0.0
 */
final class DirectInjectionParameterResolver implements ParameterResolverInterface
{
    public function __construct(
        private readonly object $object,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return;
        }

        if (is_a($this->object, $type->getName())) {
            yield $this->object;
        }
    }

    public function getWeight(): int
    {
        return 100;
    }
}
