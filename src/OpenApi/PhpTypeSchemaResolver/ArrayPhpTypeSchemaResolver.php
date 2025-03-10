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
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerAwareInterface;
use Sunrise\Http\Router\OpenApi\OpenApiPhpTypeSchemaResolverManagerInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Hydrator\Annotation\Subtype;

/**
 * @since 3.0.0
 */
final class ArrayPhpTypeSchemaResolver implements
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
        return $phpType->name == Type::PHP_TYPE_NAME_ARRAY;
    }

    /**
     * @inheritDoc
     */
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        $phpTypeSchema = [
            'oneOf' => [
                [
                    'type' => Type::OAS_TYPE_NAME_ARRAY,
                ],
                [
                    'type' => Type::OAS_TYPE_NAME_OBJECT,
                ],
            ],
        ];

        if (
            $phpTypeHolder instanceof ReflectionParameter ||
            $phpTypeHolder instanceof ReflectionProperty
        ) {
            /** @var list<ReflectionAttribute<Subtype>> $annotations */
            $annotations = $phpTypeHolder->getAttributes(Subtype::class);
            if (isset($annotations[0])) {
                $annotation = $annotations[0]->newInstance();

                $arrayElementPhpType = new Type($annotation->name, $annotation->allowsNull);
                $arrayElementPhpTypeSchema = $this->openApiPhpTypeSchemaResolverManager
                    ->resolvePhpTypeSchema($arrayElementPhpType, $phpTypeHolder);

                $phpTypeSchema['oneOf'][0]['items'] = $arrayElementPhpTypeSchema;
                $phpTypeSchema['oneOf'][1]['additionalProperties'] = $arrayElementPhpTypeSchema;

                if ($annotation->limit !== null) {
                    $phpTypeSchema['oneOf'][0]['maxItems'] = $annotation->limit;
                    $phpTypeSchema['oneOf'][1]['maxProperties'] = $annotation->limit;
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
