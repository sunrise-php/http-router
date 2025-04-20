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

use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use SensitiveParameter;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;

/**
 * @since 3.0.0
 */
final class StringPhpTypeSchemaResolver implements OpenApiPhpTypeSchemaResolverInterface
{
    public function supportsPhpType(Type $phpType, Reflector $phpTypeHolder): bool
    {
        return $phpType->name === Type::PHP_TYPE_NAME_STRING;
    }

    /**
     * @inheritDoc
     */
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
        }

        if ($phpTypeHolder instanceof ReflectionProperty) {
            foreach ($phpTypeHolder->getDeclaringClass()->getConstructor()?->getParameters() ?? [] as $parameter) {
                if ($parameter->name === $phpTypeHolder->name) {
                    if ($parameter->isPromoted() && $parameter->getAttributes(SensitiveParameter::class) !== []) {
                        $phpTypeSchema['format'] = 'password';
                    }

                    break;
                }
            }
        }

        return $phpTypeSchema;
    }

    public function getWeight(): int
    {
        return 0;
    }
}
