<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Exception;

/**
 * Import classes
 */
use ReflectionParameter;

/**
 * Import functions
 */
use function sprintf;

/**
 * ParameterResolvingException
 *
 * @since 3.0.0
 */
class ParameterResolvingException extends Exception
{

    /**
     * @return self
     */
    final public static function unsupportedParameterTypeDeclaration(ReflectionParameter $parameter): self
    {
        return new self(sprintf(
            '%s($%s[%d]): Unsupported parameter type declaration',
            $parameter->getDeclaringFunction()->getName(),
            $parameter->getName(),
            $parameter->getPosition()
        ));
    }

    /**
     * @return self
     */
    final public static function unknownParameter(ReflectionParameter $parameter): self
    {
        return new self(sprintf(
            '%s($%s[%d]): Unknown parameter',
            $parameter->getDeclaringFunction()->getName(),
            $parameter->getName(),
            $parameter->getPosition()
        ));
    }
}
