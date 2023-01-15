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
use ReflectionParameter;
use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\Exception\ParameterResolvingException;

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
     * Creates a new instance of the resolutioner with the given data for resolve a non-built-in typed parameter
     *
     * Please note that this method MUST NOT change the object state.
     *
     * @param class-string<T> $type
     * @param T $value
     *
     * @return static
     *
     * @template T
     */
    public function withType(string $type, object $value): ParameterResolutionerInterface;

    /**
     * Adds the given parameter resolver(s) to the resolutioner
     *
     * @param ParameterResolverInterface ...$resolvers
     *
     * @return void
     */
    public function addResolver(ParameterResolverInterface ...$resolvers): void;

    /**
     * Sets the given container to the resolutioner for resolve non-built-in typed parameters
     *
     * @param ContainerInterface|null $container
     *
     * @return void
     */
    public function setContainer(?ContainerInterface $container): void;

    /**
     * Resolves the given parameter(s) to arguments
     *
     * @param ReflectionParameter ...$parameters
     *
     * @return list<mixed>
     *         List of ready-to-pass arguments.
     *
     * @throws ParameterResolvingException
     *         If one of the parameters cannot be resolved to an argument.
     */
    public function resolveParameters(ReflectionParameter ...$parameters): array;
}
