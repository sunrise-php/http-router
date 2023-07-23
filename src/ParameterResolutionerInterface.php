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

use Psr\Http\Message\RequestInterface;
use Sunrise\Http\Router\Exception\ResolvingParameterException;
use Sunrise\Http\Router\ParameterResolver\ParameterResolverInterface;
use ReflectionParameter;

/**
 * ParameterResolutionerInterface
 *
 * @since 3.0.0
 */
interface ParameterResolutionerInterface
{

    /**
     * Creates a new instance of the resolutioner with the given current request
     *
     * Please note that this method MUST NOT change the object state.
     *
     * @param RequestInterface $request
     *
     * @return static
     */
    public function withRequest(RequestInterface $request): static;

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
     * @return list<mixed> List of ready-to-pass arguments.
     *
     * @throws ResolvingParameterException If one of the parameters cannot be resolved to an argument.
     */
    public function resolveParameters(ReflectionParameter ...$parameters): array;
}
