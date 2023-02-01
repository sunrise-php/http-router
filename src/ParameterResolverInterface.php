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
use Sunrise\Http\Router\Exception\ResolvingParameterException;
use ReflectionParameter;

/**
 * ParameterResolverInterface
 *
 * @since 3.0.0
 */
interface ParameterResolverInterface
{

    /**
     * Checks if the given parameter is supported
     *
     * @param ReflectionParameter $parameter
     * @param mixed $context
     *
     * @return bool
     */
    public function supportsParameter(ReflectionParameter $parameter, $context): bool;

    /**
     * Resolves the given parameter to an argument
     *
     * @param ReflectionParameter $parameter
     * @param mixed $context
     *
     * @return mixed
     *
     * @throws ResolvingParameterException
     *         If the parameter cannot be resolved to an argument.
     */
    public function resolveParameter(ReflectionParameter $parameter, $context);
}
