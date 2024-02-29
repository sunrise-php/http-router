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

/**
 * @since 3.0.0
 */
interface ParameterResolverInterface
{
    /**
     * @return Generator<int, mixed>
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator;
}
