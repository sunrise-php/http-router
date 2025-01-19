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
use ReflectionProperty;
use Reflector;
use Sunrise\Http\Router\OpenApi\NamedPhpTypeSchemaInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverChainInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Hydrator\Annotation\Alias;
use Sunrise\Hydrator\Annotation\DefaultValue;
use Sunrise\Hydrator\Annotation\Ignore;

use function class_exists;
use function strtr;

/**
 * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#relationship
 *
 * @since 3.0.0
 */
final class ObjectNamedPhpTypeSchemaResolver implements PhpTypeSchemaResolverInterface, NamedPhpTypeSchemaInterface
{
    public function __construct(
        private readonly PhpTypeSchemaResolverChainInterface $phpTypeSchemaResolverChain,
    ) {
    }

    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): ?array
    {
        if (!class_exists($phpType->name)) {
            return null;
        }

        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
        ];

        if ($phpType->allowsNull) {
            $schema['nullable'] = true;
        }

        $class = new ReflectionClass($phpType->name);

        foreach ($class->getProperties() as $property) {
            if (self::isIgnoredProperty($property)) {
                continue;
            }

            $propertyName = self::getPropertyName($property);
            $propertySchema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema($property->getType(), $property);

            $schema['properties'][$propertyName] = $propertySchema;

            if (!self::isOptionalProperty($property)) {
                $schema['required'][] = $propertyName;
            }
        }

        return $schema;
    }

    public function getWeight(): int
    {
        return -100;
    }

    public function getPhpTypeSchemaName(Type $type): string
    {
        return strtr($type->name, '\\', '.');
    }

    /**
     * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#ignored-property
     */
    private static function isIgnoredProperty(ReflectionProperty $property): bool
    {
        return $property->getAttributes(Ignore::class) !== [];
    }

    /**
     * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#property-alias
     */
    private static function getPropertyName(ReflectionProperty $property): string
    {
        /** @var list<ReflectionAttribute<Alias>> $annotations */
        $annotations = $property->getAttributes(Alias::class);

        return $annotations[0]?->newInstance()->value ?? $property->getName();
    }

    /**
     * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#optional
     */
    private static function isOptionalProperty(ReflectionProperty $property): bool
    {
        if ($property->hasDefaultValue()) {
            return true;
        }

        if ($property->getAttributes(DefaultValue::class) !== []) {
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
}
