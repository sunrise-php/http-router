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
use Sunrise\Http\Router\Exception\InvalidArgumentException;
use Sunrise\Http\Router\Exception\LogicException;

/**
 * ClassResolverInterface
 *
 * @since 3.0.0
 *
 * @template T of object
 */
interface ClassResolverInterface
{

    /**
     * Resolves the given named class
     *
     * @param class-string<T> $className
     *
     * @return T
     *
     * @throws InvalidArgumentException
     *         If the class doesn't exist.
     *
     * @throws LogicException
     *         If the class cannot be resolved.
     */
    public function resolveClass(string $className): object;
}
