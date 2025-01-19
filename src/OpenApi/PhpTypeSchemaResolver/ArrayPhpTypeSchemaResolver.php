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
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverChainAwareInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverChainInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Hydrator\Annotation\Subtype;

/**
 * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#array
 * @link https://swagger.io/docs/specification/v3_0/data-models/data-types/#arrays
 * @link https://swagger.io/docs/specification/v3_0/data-models/data-types/#objects
 *
 * @since 3.0.0
 */
final class ArrayPhpTypeSchemaResolver implements
    PhpTypeSchemaResolverInterface,
    PhpTypeSchemaResolverChainAwareInterface
{
    private readonly PhpTypeSchemaResolverChainInterface $phpTypeSchemaResolverChain;

    public function setPhpTypeSchemaResolverChain(PhpTypeSchemaResolverChainInterface $phpTypeSchemaResolverChain): void
    {
        $this->phpTypeSchemaResolverChain = $phpTypeSchemaResolverChain;
    }

    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): ?array
    {
        if ($phpType->name !== Type::PHP_TYPE_NAME_ARRAY) {
            return null;
        }

        $schema = [
            'oneOf' => [
                [
                    'type' => Type::OAS_TYPE_NAME_ARRAY,
                ],
                [
                    'type' => Type::OAS_TYPE_NAME_OBJECT,
                ],
            ],
        ];

        if ($phpTypeHolder instanceof ReflectionParameter || $phpTypeHolder instanceof ReflectionProperty) {
            /** @var list<ReflectionAttribute<Subtype>> $annotations */
            $annotations = $phpTypeHolder->getAttributes(Subtype::class);
            if (isset($annotations[0])) {
                $annotation = $annotations[0]->newInstance();
                $elementType = new Type($annotation->name, $annotation->allowsNull);
                $elementSchema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema($elementType, $phpTypeHolder);
                $schema['oneOf'][0]['items'] = $elementSchema;
                $schema['oneOf'][1]['additionalProperties'] = $elementSchema;
                if ($annotation->limit !== null) {
                    $schema['oneOf'][0]['maxItems'] = $annotation->limit;
                    $schema['oneOf'][1]['maxProperties'] = $annotation->limit;
                }
            }
        }

        return $schema;
    }

    public function getWeight(): int
    {
        return 0;
    }
}
