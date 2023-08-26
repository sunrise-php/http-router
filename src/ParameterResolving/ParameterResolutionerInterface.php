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

namespace Sunrise\Http\Router\ParameterResolving;

use Generator;
use ReflectionParameter;
use Sunrise\Http\Router\ParameterResolving\ParameterResolver\ParameterResolverInterface;

/**
 * ParameterResolutionerInterface
 *
 * @since 3.0.0
 */
interface ParameterResolutionerInterface
{

    /**
     * Creates a new instance of the resolutioner with the given context
     *
     * Please note that this method MUST NOT change the object state.
     *
     * @param mixed $context
     *
     * @return static
     */
    public function withContext(mixed $context): static;

    /**
     * Creates a new instance of the resolutioner with the given priority parameter resolver(s)
     *
     * Please note that this method MUST NOT change the object state.
     *
     * @param ParameterResolverInterface ...$resolvers
     *
     * @return static
     */
    public function withPriorityResolver(ParameterResolverInterface ...$resolvers): static;

    /**
     * Adds the given parameter resolver(s) to the resolutioner
     *
     * @param ParameterResolverInterface ...$resolvers
     *
     * @return void
     */
    public function addResolver(ParameterResolverInterface ...$resolvers): void;

    /**
     * Resolves the given parameter(s) to arguments
     *
     * @param ReflectionParameter ...$parameters
     *
     * @return Generator<int, mixed> List of ready-to-pass arguments.
     */
    public function resolveParameters(ReflectionParameter ...$parameters): Generator;
}
