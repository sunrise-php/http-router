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

namespace Sunrise\Http\Router\OpenApi;

use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.0.0
 */
interface OpenApiOperationEnricherManagerInterface
{
    /**
     * @param ReflectionClass<object>|ReflectionMethod $requestHandler
     * @param array<array-key, mixed> $operation
     * @param-out array<array-key, mixed> $operation
     */
    public function enrichOperation(
        RouteInterface $route,
        ReflectionClass|ReflectionMethod $requestHandler,
        array &$operation,
    ): void;
}
