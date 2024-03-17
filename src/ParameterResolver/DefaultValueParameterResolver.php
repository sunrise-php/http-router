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
use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolverInterface;

/**
 * @since 3.0.0
 */
final class DefaultValueParameterResolver implements ParameterResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        if ($parameter->isDefaultValueAvailable()) {
            yield $parameter->getDefaultValue();
        }
    }

    public function getWeight(): int
    {
        return -1000;
    }
}
