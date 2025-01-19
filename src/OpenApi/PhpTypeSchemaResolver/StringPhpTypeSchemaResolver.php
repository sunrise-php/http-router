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

use ReflectionAttribute;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use SensitiveParameter;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Symfony\Component\Validator\Constraints;

/**
 * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#string
 * @link https://swagger.io/docs/specification/v3_0/data-models/data-types/#strings
 *
 * @since 3.0.0
 */
final class StringPhpTypeSchemaResolver implements PhpTypeSchemaResolverInterface
{
    public function supportsPhpType(Type $phpType, Reflector $phpTypeHolder): bool
    {
        return $phpType->name === Type::PHP_TYPE_NAME_STRING;
    }

    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        $phpTypeSchema = [
            'type' => Type::OAS_TYPE_NAME_STRING,
        ];

        if ($phpTypeHolder instanceof ReflectionParameter) {
            if ($phpTypeHolder->getAttributes(SensitiveParameter::class) !== []) {
                $phpTypeSchema['format'] = 'password';
            }
        } elseif ($phpTypeHolder instanceof ReflectionProperty) {
            if ($phpTypeHolder->getAttributes(Constraints\Email::class, ReflectionAttribute::IS_INSTANCEOF) !== []) {
                $phpTypeSchema['format'] = 'email';
            } elseif ($phpTypeHolder->getAttributes(Constraints\Uuid::class, ReflectionAttribute::IS_INSTANCEOF) !== []) {
                $phpTypeSchema['format'] = 'uuid';
            }
        }

        if ($phpType->allowsNull) {
            $phpTypeSchema['nullable'] = true;
        }

        return $phpTypeSchema;
    }

    public function getWeight(): int
    {
        return 0;
    }
}
