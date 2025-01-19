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

use BackedEnum;
use ReflectionEnum;
use ReflectionException;
use Reflector;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;

use function is_subclass_of;

/**
 * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#php-81-built-in-enumerations
 * @link https://swagger.io/docs/specification/v3_0/data-models/enums/
 *
 * @since 3.0.0
 */
final class BackedEnumPhpTypeSchemaResolver implements PhpTypeSchemaResolverInterface
{
    public function supportsPhpType(Type $phpType, Reflector $phpTypeHolder): bool
    {
        return is_subclass_of($phpType->name, BackedEnum::class);
    }

    /**
     * @inheritDoc
     *
     * @throws ReflectionException
     */
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        /** @var class-string<BackedEnum> $phpTypeName */
        $phpTypeName = $phpType->name;

        $phpTypeSchema = [
            'type' => match ((string) (new ReflectionEnum($phpTypeName))->getBackingType()) {
                Type::PHP_TYPE_NAME_INT => Type::OAS_TYPE_NAME_INTEGER,
                Type::PHP_TYPE_NAME_STRING => Type::OAS_TYPE_NAME_STRING,
            },
        ];

        $phpTypeSchema['enum'] = [];
        foreach ($phpTypeName::cases() as $case) {
            $phpTypeSchema['enum'][] = $case->value;
        }

        // https://swagger.io/docs/specification/v3_0/data-models/enums/#nullable-enums
        if ($phpType->allowsNull) {
            $phpTypeSchema['enum'][] = null;
            $phpTypeSchema['nullable'] = true;
        }

        return $phpTypeSchema;
    }

    public function getWeight(): int
    {
        return 0;
    }
}
