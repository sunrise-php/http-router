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

use Sunrise\Http\Router\Helper\RouteSimplifier;
use Sunrise\Http\Router\RouteInterface;

/**
 * @since 3.3.0
 */
final class OpenApiPathBuilder implements OpenApiPathBuilderInterface
{
    public function buildPath(RouteInterface $route): string
    {
        return RouteSimplifier::simplifyRoute($route->getPath());
    }
}
