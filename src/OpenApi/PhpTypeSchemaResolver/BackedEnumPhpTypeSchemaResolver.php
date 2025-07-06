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
use ReflectionEnum;
use ReflectionException;
use Reflector;
use Sunrise\Http\Router\OpenApi\Annotation\SchemaName;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaNameResolverInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeFactory;

use function is_subclass_of;

/**
 * @since 3.0.0
 */
final class BackedEnumPhpTypeSchemaResolver implements
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

        $enumPhpType = TypeFactory::fromPhpTypeReflection((new ReflectionEnum($phpTypeName))->getBackingType());
        $phpTypeSchema = $this->openApiPhpTypeSchemaResolverManager->resolvePhpTypeSchema($enumPhpType, $phpTypeHolder);

        $phpTypeSchema['enum'] = [];
        foreach ($phpTypeName::cases() as $case) {
            $phpTypeSchema['enum'][] = $case->value;
        }

        return $phpTypeSchema;
    }

    public function getWeight(): int
    {
        return 0;
    }

    public function resolvePhpTypeSchemaName(Type $phpType, Reflector $phpTypeHolder): string
    {
        /** @var class-string $className */
        $className = $phpType->name;
        $classReflection = new ReflectionClass($className);

        /** @var list<ReflectionAttribute<SchemaName>> $annotations */
        $annotations = $classReflection->getAttributes(SchemaName::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            return $annotation->value;
        }

        return $classReflection->getShortName();
    }
}
