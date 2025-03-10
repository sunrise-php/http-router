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

namespace Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver;

use Ramsey\Uuid\UuidInterface;
use Reflector;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;

/**
 * @since 3.0.0
 */
final class RamseyUuidPhpTypeSchemaResolver implements OpenApiPhpTypeSchemaResolverInterface
{
    public function supportsPhpType(Type $phpType, Reflector $phpTypeHolder): bool
    {
        return $phpType->name === UuidInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        return [
            'type' => Type::OAS_TYPE_NAME_STRING,
            'format' => 'uuid',
        ];
    }

    public function getWeight(): int
    {
        return 0;
    }
}
