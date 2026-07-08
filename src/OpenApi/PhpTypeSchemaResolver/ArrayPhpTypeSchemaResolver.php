<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatolii Nekhai <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatolii Nekhai
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver;

use phpDocumentor\Reflection as PhpDoc;
use ReflectionAttribute;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeInterface;
use Sunrise\Hydrator\Annotation\Subtype;

/**
 * @since 3.0.0
 */
final class ArrayPhpTypeSchemaResolver implements
    OpenApiPhpTypeSchemaResolverInterface,
    OpenApiPhpTypeSchemaResolverManagerAwareInterface
{
    private readonly OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager;
    private readonly PhpDoc\DocBlockFactoryInterface $docBlockFactory;
    private readonly PhpDoc\Types\ContextFactory $docBlockContextFactory;

    public function __construct()
    {
        $this->docBlockFactory = PhpDoc\DocBlockFactory::createInstance();
        $this->docBlockContextFactory = new PhpDoc\Types\ContextFactory();
    }

    public function setOpenApiPhpTypeSchemaResolverManager(
        OpenApiPhpTypeSchemaResolverManagerInterface $openApiPhpTypeSchemaResolverManager,
    ): void {
        $this->openApiPhpTypeSchemaResolverManager = $openApiPhpTypeSchemaResolverManager;
    }

    public function supportsPhpType(TypeInterface $phpType, Reflector $phpTypeHolder): bool
    {
        return $phpType->getName() == Type::PHP_TYPE_NAME_ARRAY;
    }

    /**
     * @inheritDoc
     */
    public function resolvePhpTypeSchema(TypeInterface $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        $phpTypeSchema = [
            'type' => Type::OAS_TYPE_NAME_ARRAY,
        ];

        if (
            $phpTypeHolder instanceof ReflectionParameter ||
            $phpTypeHolder instanceof ReflectionProperty
        ) {
            /** @var list<ReflectionAttribute<Subtype>> $annotations */
            $annotations = $phpTypeHolder->getAttributes(Subtype::class, ReflectionAttribute::IS_INSTANCEOF);
            if (isset($annotations[0])) {
                $annotation = $annotations[0]->newInstance();

                $itemPhpType = new Type($annotation->name, $annotation->allowsNull);
                $phpTypeSchema['items'] = $this->openApiPhpTypeSchemaResolverManager
                    ->resolvePhpTypeSchema($itemPhpType, $phpTypeHolder);

                if ($annotation->limit !== null) {
                    $phpTypeSchema['maxItems'] = $annotation->limit;
                }
            }
        }

        if (!isset($phpTypeSchema['items'])) {
            $itemPhpType = $this->getItemTypeFromDocBlock($phpTypeHolder);
            if ($itemPhpType !== null) {
                $phpTypeSchema['items'] = $this->openApiPhpTypeSchemaResolverManager
                    ->resolvePhpTypeSchema($itemPhpType, $phpTypeHolder);
            }
        }

        return $phpTypeSchema;
    }

    public function getWeight(): int
    {
        return 0;
    }

    private function getItemTypeFromDocBlock(Reflector $phpTypeHolder): ?TypeInterface
    {
        if (! $phpTypeHolder instanceof ReflectionProperty) {
            return null;
        }

        $docComment = $phpTypeHolder->getDocComment();
        if ($docComment === false) {
            return null;
        }

        $docBlock = $this->docBlockFactory->create(
            $docComment,
            $this->docBlockContextFactory->createFromReflector($phpTypeHolder),
        );

        $varTags = $docBlock->getTagsByName('var');
        if ($varTags === [] || ! $varTags[0] instanceof PhpDoc\DocBlock\Tags\Var_) {
            return null;
        }

        $varType = $varTags[0]->getType();
        if ($varType === null) {
            return null;
        }

        $varType = self::unwrapNullablePhpDocType($varType);
        if (! $varType instanceof PhpDoc\Types\AbstractList) {
            return null;
        }

        $itemType = $varType->getValueType();

        return new Type(
            name: \ltrim((string) self::unwrapNullablePhpDocType($itemType), '\\'),
            allowsNull: self::isNullablePhpDocType($itemType),
        );
    }

    private static function unwrapNullablePhpDocType(PhpDoc\Type $phpDocType): PhpDoc\Type
    {
        if ($phpDocType instanceof PhpDoc\Types\Nullable) {
            return $phpDocType->getActualType();
        }

        if ($phpDocType instanceof PhpDoc\Types\Compound) {
            $types = [];
            foreach ($phpDocType as $type) {
                if (! $type instanceof PhpDoc\Types\Null_) {
                    $types[] = $type;
                }
            }

            if (\count($types) === 1) {
                return $types[0];
            }
        }

        return $phpDocType;
    }

    private static function isNullablePhpDocType(PhpDoc\Type $phpDocType): bool
    {
        if ($phpDocType instanceof PhpDoc\Types\Nullable) {
            return true;
        }

        if ($phpDocType instanceof PhpDoc\Types\Compound) {
            foreach ($phpDocType as $type) {
                if ($type instanceof PhpDoc\Types\Null_) {
                    return true;
                }
            }
        }

        return false;
    }
}
