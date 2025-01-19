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

use DateTimeImmutable;
use ReflectionAttribute;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use Sunrise\Http\Router\OpenApi\NamedPhpTypeSchemaInterface;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverInterface;
use Sunrise\Hydrator\Annotation\Format;

use function date;
use function is_a;

/**
 * @link https://github.com/sunrise-php/hydrator/blob/5b8e8bf51c5795b741fbae28258eadc8be16d7c2/README.md#timestamp
 * @link https://swagger.io/docs/specification/v3_0/data-models/data-types/#strings
 *
 * @since 3.0.0
 */
final class TimestampNamedPhpTypeSchemaResolver implements PhpTypeSchemaResolverInterface, NamedPhpTypeSchemaInterface
{
    public function __construct(
        private readonly OpenApiConfiguration $openApiConfiguration,
    ) {
    }

    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): ?array
    {
        if (!is_a($phpType->name, DateTimeImmutable::class, true)) {
            return null;
        }

        $timestampFormat = $this->openApiConfiguration->timestampFormat;

        if ($phpTypeHolder instanceof ReflectionParameter || $phpTypeHolder instanceof ReflectionProperty) {
            /** @var list<ReflectionAttribute<Format>> $annotations */
            $annotations = $phpTypeHolder->getAttributes(Format::class);
            if (isset($annotations[0])) {
                $annotation = $annotations[0]->newInstance();
                $timestampFormat = $annotation->value;
            }
        }

        $schema = [
            'type' => Type::OAS_TYPE_NAME_STRING,
            'format' => 'date-time',
            'example' => date($timestampFormat, 0),
        ];

        if ($phpType->allowsNull) {
            $schema['nullable'] = true;
        }

        return $schema;
    }

    public function getWeight(): int
    {
        return 0;
    }

    public function getPhpTypeSchemaName(Type $type): string
    {
        return '@timestamp';
    }
}
