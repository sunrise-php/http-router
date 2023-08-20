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

use function is_a;

/**
 * PresetObjectParameterResolver
 *
 * @since 3.0.0
 */
final class PresetObjectParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param object $object
     */
    public function __construct(private object $object)
    {
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

        if (! is_a($this->object, $type->getName())) {
            return;
        }

        yield $this->object;
    }
}
