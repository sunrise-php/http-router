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

namespace Sunrise\Http\Router\ParameterResolving\ParameterResolver;

use Generator;
use ReflectionParameter;

/**
 * ParameterResolverInterface
 *
 * @since 3.0.0
 */
interface ParameterResolverInterface
{

    /**
     * Resolves the given parameter to an argument(s)
     *
     * @param ReflectionParameter $parameter
     * @param mixed $context
     *
     * @return Generator<int, mixed>
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator;
}
