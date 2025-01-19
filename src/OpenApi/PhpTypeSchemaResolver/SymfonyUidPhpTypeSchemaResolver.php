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

use Reflector;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

use function is_subclass_of;

/**
 * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#using-the-symfonyuid-package
 * @link https://swagger.io/docs/specification/v3_0/data-models/data-types/#strings
 *
 * @since 3.0.0
 */
final class SymfonyUidPhpTypeSchemaResolver implements PhpTypeSchemaResolverInterface
{
    public function supportsPhpType(Type $phpType, Reflector $phpTypeHolder): bool
    {
        return is_subclass_of($phpType->name, AbstractUid::class);
    }

    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        $schema = [
            'type' => Type::OAS_TYPE_NAME_STRING,
        ];

        if (is_subclass_of($phpType->name, Uuid::class)) {
            $schema['format'] = 'uuid';
        }

        return $schema;
    }

    public function getWeight(): int
    {
        return 0;
    }
}
