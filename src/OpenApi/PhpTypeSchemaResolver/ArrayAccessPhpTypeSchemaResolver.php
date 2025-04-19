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

use ArrayAccess;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Hydrator\Annotation\Subtype;

use function end;
use function is_subclass_of;

/**
 * @since 3.0.0
 */
final class ArrayAccessPhpTypeSchemaResolver implements
    OpenApiPhpTypeSchemaResolverInterface,
    OpenApiPhpTypeSchemaResolverManagerAwareInterface
{
    private readonly OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager;

    public function setOpenApiPhpTypeSchemaResolverManager(
        OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager,
    ): void {
        $this->openApiPhpTypeSchemaResolverManager = $openApiPhpTypeSchemaResolverManager;
    }

    public function supportsPhpType(Type $phpType, Reflector $phpTypeHolder): bool
    {
        return is_subclass_of($phpType->name, ArrayAccess::class);
    }

    /**
     * @inheritDoc
     *
     * @throws ReflectionException
     */
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        /** @var class-string<ArrayAccess<array-key, mixed>> $phpTypeName */
        $phpTypeName = $phpType->name;

        $arrayPhpType = new Type(Type::PHP_TYPE_NAME_ARRAY, $phpType->allowsNull);
        /** @var array{oneOf: array{0: array{type: 'array'}, 1: array{type: 'object'}}} $phpTypeSchema */
        $phpTypeSchema = $this->openApiPhpTypeSchemaResolverManager
            ->resolvePhpTypeSchema($arrayPhpType, $phpTypeHolder);

        if (
            ! $phpTypeHolder instanceof ReflectionParameter
            && ! $phpTypeHolder instanceof ReflectionProperty
            || $phpTypeHolder->getAttributes(Subtype::class) === []
        ) {
            $collectionElementPhpType = self::getCollectionElementPhpType($phpTypeName);
            $collectionElementPhpTypeSchema = $this->openApiPhpTypeSchemaResolverManager
                ->resolvePhpTypeSchema($collectionElementPhpType, $phpTypeHolder);

            $phpTypeSchema['oneOf'][0]['items'] = $collectionElementPhpTypeSchema;
            $phpTypeSchema['oneOf'][1]['additionalProperties'] = $collectionElementPhpTypeSchema;
        }

        return $phpTypeSchema;
    }

    public function getWeight(): int
    {
        return 0;
    }

    /**
     * @param class-string $className
     *
     * @throws ReflectionException
     */
    private static function getCollectionElementPhpType(string $className): Type
    {
        $constructorParameters = (new ReflectionClass($className))->getConstructor()?->getParameters() ?? [];

        /** @var ReflectionParameter|false $lastConstructorParameter */
        $lastConstructorParameter = end($constructorParameters);

        return ($lastConstructorParameter instanceof ReflectionParameter && $lastConstructorParameter->isVariadic())
            ? TypeFactory::fromPhpTypeReflection($lastConstructorParameter->getType())
            : TypeFactory::mixedPhpType();
    }
}
