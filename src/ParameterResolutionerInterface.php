<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Sunrise\Http\Router\Exception\LogicException;
use ReflectionParameter;

/**
 * ParameterResolutionerInterface
 *
 * @since 3.0.0
 */
interface ParameterResolutionerInterface
{

    /**
     * Creates a new instance of the resolutioner with the given current context
     *
     * Please note that this method MUST NOT change the object state.
     *
     * @param mixed $context
     *
     * @return static
     */
    public function withContext($context): ParameterResolutionerInterface;

    /**
     * Creates a new instance of the resolutioner with the given priority parameter resolver(s)
     *
     * Please note that this method MUST NOT change the object state.
     *
     * @param ParameterResolverInterface ...$resolvers
     *
     * @return static
     */
    public function withPriorityResolver(ParameterResolverInterface ...$resolvers): ParameterResolutionerInterface;

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
     * @return list<mixed>
     *         List of ready-to-pass arguments.
     *
     * @throws LogicException
     *         If one of the parameters cannot be resolved to an argument.
     */
    public function resolveParameters(ReflectionParameter ...$parameters): array;
}
