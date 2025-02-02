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

namespace Sunrise\Http\Router\Annotation;

use Attribute;

/**
 * @since 3.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class ApiRoute extends Route
{
    /**
     * @param string|array<array-key, string> $method
     */
    public function __construct(string $name = '', string $path = '', string|array $method = [])
    {
        parent::__construct(name: $name, path: $path, methods: (array) $method, isApiRoute: true);
    }
}
