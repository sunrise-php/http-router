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

use BackedEnum;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use ReflectionException;
use Reflector;
use Sunrise\Http\Router\OpenApi\Annotation\SchemaNameInterface;
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaNameResolverInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\TypeFactory;
use Sunrise\Http\Router\OpenApi\TypeInterface;

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

    public function supportsPhpType(TypeInterface $phpType, Reflector $phpTypeHolder): bool
    {
        return is_subclass_of($phpType->getName(), BackedEnum::class);
    }

    /**
     * @inheritDoc
     *
     * @throws ReflectionException
     */
    public function resolvePhpTypeSchema(TypeInterface $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        /** @var class-string<BackedEnum> $phpTypeName */
        $phpTypeName = $phpType->getName();

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

    public function resolvePhpTypeSchemaName(TypeInterface $phpType, Reflector $phpTypeHolder): string
    {
        /** @var class-string $className */
        $className = $phpType->getName();
        $classReflection = new ReflectionClass($className);

        /** @var list<ReflectionAttribute<SchemaNameInterface>> $annotations */
        $annotations = $classReflection->getAttributes(SchemaNameInterface::class, ReflectionAttribute::IS_INSTANCEOF);
        if (isset($annotations[0])) {
            return $annotations[0]->newInstance()->getSchemaName();
        }

        return $classReflection->getShortName();
    }
}
