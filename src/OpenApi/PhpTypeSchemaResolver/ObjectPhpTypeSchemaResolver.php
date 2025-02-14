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
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Reflector;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaNameResolverInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Hydrator\Annotation\Alias;
use Sunrise\Hydrator\Annotation\DefaultValue;
use Sunrise\Hydrator\Annotation\Ignore;
use Sunrise\Hydrator\TypeConverter\ObjectTypeConverter;

use function class_exists;
use function is_scalar;
use function strtr;

/**
 * @since 3.0.0
 */
final class ObjectPhpTypeSchemaResolver implements
    OpenApiPhpTypeSchemaResolverInterface,
    OpenApiPhpTypeSchemaNameResolverInterface,
    OpenApiPhpTypeSchemaResolverManagerAwareInterface
{
    private readonly OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager;

    public function setOpenApiPhpTypeSchemaResolverManager(
        OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager,
    ): void {
        $this->openApiPhpTypeSchemaResolverManager = $openApiPhpTypeSchemaResolverManager;
    }

    /**
     * @see ObjectTypeConverter
     */
    public function supportsPhpType(Type $phpType, Reflector $phpTypeHolder): bool
    {
        $className = $phpType->name;
        if (!class_exists($className)) {
            return false;
        }

        $class = new ReflectionClass($className);
        if ($class->isInternal() || !$class->isInstantiable()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     *
     * @throws ReflectionException
     */
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        /** @var class-string $phpTypeName */
        $phpTypeName = $phpType->name;

        $phpTypeSchema = [
            'type' => 'object',
            'additionalProperties' => false,
        ];

        $class = new ReflectionClass($phpTypeName);

        foreach ($class->getProperties() as $property) {
            if (self::isIgnoredProperty($property)) {
                continue;
            }

            $propertyType = TypeFactory::fromPhpTypeReflection($property->getType());
            $propertyTypeSchema = $this->openApiPhpTypeSchemaResolverManager
                ->resolvePhpTypeSchema($propertyType, $property);

            $propertyDefaultValue = self::getPropertyDefaultValue($property);
            $normalizePropertyDefaultValue = self::normalizePropertyDefaultValue($propertyDefaultValue);
            if ($normalizePropertyDefaultValue !== null) {
                $propertyTypeSchema = [
                    'allOf' => [
                        $propertyTypeSchema,
                    ],
                    'default' => $normalizePropertyDefaultValue,
                ];
            }

            $propertyName = self::getPropertyName($property);
            $phpTypeSchema['properties'][$propertyName] = $propertyTypeSchema;

            if (!self::hasPropertyDefaultValue($property)) {
                $phpTypeSchema['required'][] = $propertyName;
            }
        }

        return $phpTypeSchema;
    }

    public function getWeight(): int
    {
        return -100;
    }

    public function resolvePhpTypeSchemaName(Type $phpType, Reflector $phpTypeHolder): string
    {
        return strtr($phpType->name, '\\', '.');
    }

    private static function isIgnoredProperty(ReflectionProperty $property): bool
    {
        return $property->getAttributes(Ignore::class) !== [];
    }

    private static function getPropertyName(ReflectionProperty $property): string
    {
        /** @var list<ReflectionAttribute<Alias>> $annotations */
        $annotations = $property->getAttributes(Alias::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            return $annotation->value;
        }

        return $property->name;
    }

    private static function hasPropertyDefaultValue(ReflectionProperty $property): bool
    {
        if ($property->hasDefaultValue()) {
            return true;
        }

        if ($property->isPromoted()) {
            foreach ($property->getDeclaringClass()->getConstructor()?->getParameters() ?? [] as $parameter) {
                if ($parameter->name === $property->name && $parameter->isDefaultValueAvailable()) {
                    return true;
                }
            }
        }

        if ($property->getAttributes(DefaultValue::class) !== []) {
            return true;
        }

        return false;
    }

    private static function getPropertyDefaultValue(ReflectionProperty $property): mixed
    {
        if ($property->hasDefaultValue()) {
            return $property->getDefaultValue();
        }

        if ($property->isPromoted()) {
            foreach ($property->getDeclaringClass()->getConstructor()?->getParameters() ?? [] as $parameter) {
                if ($parameter->name === $property->name && $parameter->isDefaultValueAvailable()) {
                    return $parameter->getDefaultValue();
                }
            }
        }

        /** @var list<ReflectionAttribute<DefaultValue>> $annotations */
        $annotations = $property->getAttributes(DefaultValue::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            return $annotation->value;
        }

        return null;
    }

    private static function normalizePropertyDefaultValue(mixed $value): mixed
    {
        if (is_scalar($value)) {
            return $value;
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return null;
    }
}
