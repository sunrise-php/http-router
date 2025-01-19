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
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;

/**
 * @since 3.0.0
 */
interface PhpTypeSchemaResolverInterface
{
    public function supportsPhpType(Type $phpType, Reflector $phpTypeHolder): bool;

    /**
     * @throws UnsupportedPhpTypeException Must be thrown if the type isn't supported.
     */
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array;

    public function getWeight(): int;
}
