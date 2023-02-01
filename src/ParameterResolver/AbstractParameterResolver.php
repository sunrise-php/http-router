<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\ParameterResolver;

/**
 * Import classes
 */
use Sunrise\Http\Router\ParameterResolverInterface;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Import functions
 */
use function sprintf;

/**
 * AbstractParameterResolver
 *
 * @since 3.0.0
 */
abstract class AbstractParameterResolver implements ParameterResolverInterface
{

    /**
     * Stringifies the given parameter
     *
     * @param ReflectionParameter $parameter
     *
     * @return string
     */
    final protected function stringifyParameter(ReflectionParameter $parameter): string
    {
        if ($parameter->getDeclaringFunction() instanceof ReflectionMethod) {
            return sprintf(
                '%s::%s($%s[%d])',
                $parameter->getDeclaringFunction()->getDeclaringClass()->getName(),
                $parameter->getDeclaringFunction()->getName(),
                $parameter->getName(),
                $parameter->getPosition()
            );
        }

        return sprintf(
            '%s($%s[%d])',
            $parameter->getDeclaringFunction()->getName(),
            $parameter->getName(),
            $parameter->getPosition()
        );
    }
}
