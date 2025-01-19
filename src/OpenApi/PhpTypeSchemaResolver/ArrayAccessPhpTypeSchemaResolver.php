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
use ReflectionType;
use Reflector;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverChainAwareInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverChainInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Hydrator\Annotation\Subtype;

use function end;
use function is_subclass_of;

/**
 * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#array
 * @link https://swagger.io/docs/specification/v3_0/data-models/data-types/#arrays
 * @link https://swagger.io/docs/specification/v3_0/data-models/data-types/#objects
 *
 * @since 3.0.0
 */
final class ArrayAccessPhpTypeSchemaResolver implements
    PhpTypeSchemaResolverInterface,
    PhpTypeSchemaResolverChainAwareInterface
{
    private readonly PhpTypeSchemaResolverChainInterface $phpTypeSchemaResolverChain;

    public function setPhpTypeSchemaResolverChain(PhpTypeSchemaResolverChainInterface $phpTypeSchemaResolverChain): void
    {
        $this->phpTypeSchemaResolverChain = $phpTypeSchemaResolverChain;
    }

    /**
     * @throws ReflectionException
     */
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): ?array
    {
        if (!is_subclass_of($phpType->name, ArrayAccess::class)) {
            return null;
        }

        /** @var array{oneOf: array{0: array{type: 'array'}, 1: array{type: 'object'}}} $schema */
        $schema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema(
            new Type(Type::PHP_TYPE_NAME_ARRAY, $phpType->allowsNull),
            $phpTypeHolder,
        );

        // phpcs:ignore Generic.Files.LineLength.TooLong
        if (($phpTypeHolder instanceof ReflectionParameter || $phpTypeHolder instanceof ReflectionProperty) && $phpTypeHolder->getAttributes(Subtype::class) === []) {
            $elementType = self::getCollectionElementType($phpType->name);
            $elementSchema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema($elementType, $phpTypeHolder);
            $schema['oneOf'][0]['items'] = $elementSchema;
            $schema['oneOf'][1]['additionalProperties'] = $elementSchema;
        }

        return $schema;
    }

    public function getWeight(): int
    {
        return 0;
    }

    /**
     * @param class-string<ArrayAccess> $className
     *
     * @throws ReflectionException
     */
    private static function getCollectionElementType(string $className): ?ReflectionType
    {
        $class = new ReflectionClass($className);

        $constructor = $class->getConstructor();
        if ($constructor === null) {
            return null;
        }

        $parameters = $constructor->getParameters();
        if ($parameters === []) {
            return null;
        }

        /** @var ReflectionParameter $parameter */
        $parameter = end($parameters);
        if (!$parameter->isVariadic()) {
            return null;
        }

        return $parameter->getType();
    }
}
