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

use DateTimeZone;
use Reflector;
use Sunrise\Http\Router\OpenApi\NamedPhpTypeSchemaInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverInterface;

/**
 * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#timezone
 * @link https://swagger.io/docs/specification/v3_0/data-models/data-types/#strings
 *
 * @since 3.0.0
 */
final class TimezoneNamedPhpTypeSchemaResolver implements PhpTypeSchemaResolverInterface, NamedPhpTypeSchemaInterface
{
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): ?array
    {
        if ($phpType->name !== DateTimeZone::class) {
            return null;
        }

        $schema = [
            'type' => Type::OAS_TYPE_NAME_STRING,
            'enum' => DateTimeZone::listIdentifiers(),
        ];

        if ($phpType->allowsNull) {
            $schema['enum'][] = null;
            $schema['nullable'] = true;
        }

        return $schema;
    }

    public function getWeight(): int
    {
        return 0;
    }

    public function getPhpTypeSchemaName(Type $type): string
    {
        return '@timezone';
    }
}
