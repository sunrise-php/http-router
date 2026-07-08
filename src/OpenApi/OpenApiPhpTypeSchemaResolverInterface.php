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

namespace Sunrise\Http\Router\OpenApi;

use Reflector;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;

/**
 * @since 3.0.0
 */
interface OpenApiPhpTypeSchemaResolverInterface
{
    public function supportsPhpType(TypeInterface $phpType, Reflector $phpTypeHolder): bool;

    /**
     * @return array<array-key, mixed>
     *
     * @throws UnsupportedPhpTypeException Must be thrown if the type isn't supported.
     */
    public function resolvePhpTypeSchema(TypeInterface $phpType, Reflector $phpTypeHolder): array;

    public function getWeight(): int;
}
