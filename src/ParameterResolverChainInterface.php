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

namespace Sunrise\Http\Router;

use Generator;
use ReflectionParameter;
use Sunrise\Http\Router\Exception\InvalidParameterException;
use Sunrise\Http\Router\Exception\UnsupportedParameterException;

/**
 * @since 3.0.0
 */
interface ParameterResolverChainInterface
{
    public function withContext(mixed $context): static;

    public function withResolver(ParameterResolverInterface ...$resolvers): static;

    /**
     * @return Generator<int, mixed>
     *
     * @throws InvalidParameterException {@see ParameterResolverInterface::resolveParameter()}
     * @throws UnsupportedParameterException
     */
    public function resolveParameters(ReflectionParameter ...$parameters): Generator;
}
