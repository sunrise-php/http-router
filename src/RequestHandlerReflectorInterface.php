<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatolii Nekhai <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatolii Nekhai
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

/**
 * @since 3.0.0
 */
interface RequestHandlerReflectorInterface
{
    /**
     * @return ReflectionClass<object>|ReflectionMethod
     *
     * @throws InvalidArgumentException
     */
    public function reflectRequestHandler(mixed $reference): ReflectionClass|ReflectionMethod;
}
