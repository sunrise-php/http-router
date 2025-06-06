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

use Reflector;

/**
 * @since 3.0.0
 */
interface OpenApiPhpTypeSchemaNameResolverInterface
{
    /**
     * Please note that nullable should be ignored.
     */
    public function resolvePhpTypeSchemaName(Type $phpType, Reflector $phpTypeHolder): string;
}
