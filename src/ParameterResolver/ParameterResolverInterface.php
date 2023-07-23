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

use Psr\Http\Message\RequestInterface;
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
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function supportsParameter(ReflectionParameter $parameter, RequestInterface $request): bool;

    /**
     * Resolves the given parameter to an argument
     *
     * @param ReflectionParameter $parameter
     * @param RequestInterface $request
     *
     * @return mixed
     *
     * @throws ResolvingParameterException
     *         If the parameter cannot be resolved to an argument.
     */
    public function resolveParameter(ReflectionParameter $parameter, RequestInterface $request): mixed;
}
