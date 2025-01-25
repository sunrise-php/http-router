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
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Reflector;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaNameResolverInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverChainAwareInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverChainInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Hydrator\Annotation\Alias;
use Sunrise\Hydrator\Annotation\DefaultValue;
use Sunrise\Hydrator\Annotation\Ignore;

use function class_exists;
use function strtr;

/**
 * @since 3.0.0
 */
final class ObjectPhpTypeSchemaResolver implements
    PhpTypeSchemaResolverInterface,
    PhpTypeSchemaNameResolverInterface,
    PhpTypeSchemaResolverChainAwareInterface
{
    private readonly PhpTypeSchemaResolverChainInterface $phpTypeSchemaResolverChain;

    public function setPhpTypeSchemaResolverChain(PhpTypeSchemaResolverChainInterface $phpTypeSchemaResolverChain): void
    {
        $this->phpTypeSchemaResolverChain = $phpTypeSchemaResolverChain;
    }

    public function supportsPhpType(Type $phpType, Reflector $phpTypeHolder): bool
    {
        return class_exists($phpType->name);
    }

    /**
     * @inheritDoc
     *
     * @throws ReflectionException
     */
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        /** @var class-string<object> $phpTypeName */
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

            $propertyName = self::getPropertyName($property);
            $propertyType = TypeFactory::fromPhpTypeReflection($property->getType());
            $propertyTypeSchema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema($propertyType, $property);

            $phpTypeSchema['properties'][$propertyName] = $propertyTypeSchema;

            if (!self::isOptionalProperty($property)) {
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

        return $property->getName();
    }

    private static function isOptionalProperty(ReflectionProperty $property): bool
    {
        if (self::hasPropertyDefaultValue($property)) {
            return true;
        }

        if (!$property->isPromoted()) {
            return false;
        }

        foreach ($property->getDeclaringClass()->getConstructor()?->getParameters() ?? [] as $parameter) {
            if ($parameter->getName() === $property->getName()) {
                return $parameter->isDefaultValueAvailable();
            }
        }

        return false; // will never get here...
    }

    private static function hasPropertyDefaultValue(ReflectionProperty $property): bool
    {
        return $property->hasDefaultValue() || $property->getAttributes(DefaultValue::class) !== [];
    }
}
