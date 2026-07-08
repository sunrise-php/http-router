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

namespace Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver;

use Ramsey\Uuid\UuidInterface;
use Reflector;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeInterface;

/**
 * @since 3.0.0
 */
final class RamseyUuidPhpTypeSchemaResolver implements OpenApiPhpTypeSchemaResolverInterface
{
    public function supportsPhpType(TypeInterface $phpType, Reflector $phpTypeHolder): bool
    {
        return $phpType->getName() === UuidInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function resolvePhpTypeSchema(TypeInterface $phpType, Reflector $phpTypeHolder): array
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
