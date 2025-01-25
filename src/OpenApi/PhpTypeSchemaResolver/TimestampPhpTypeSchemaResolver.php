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
use Sunrise\Http\Router\OpenApi\Exception\UnsupportedPhpTypeException;
use Sunrise\Http\Router\OpenApi\OpenApiConfiguration;
use Sunrise\Http\Router\OpenApi\OpenApiConfigurationAwareInterface;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolverInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Hydrator\Annotation\Format;

use function date;
use function is_a;

/**
 * @since 3.0.0
 */
final class TimestampPhpTypeSchemaResolver implements
    PhpTypeSchemaResolverInterface,
    OpenApiConfigurationAwareInterface
{
    private readonly OpenApiConfiguration $openApiConfiguration;

    public function setOpenApiConfiguration(OpenApiConfiguration $openApiConfiguration): void
    {
        $this->openApiConfiguration = $openApiConfiguration;
    }

    public function supportsPhpType(Type $phpType, Reflector $phpTypeHolder): bool
    {
        return is_a($phpType->name, DateTimeImmutable::class, true);
    }

    /**
     * @inheritDoc
     */
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->supportsPhpType($phpType, $phpTypeHolder) or throw new UnsupportedPhpTypeException();

        $timestampFormat = $this->openApiConfiguration->defaultTimestampFormat;

        if (
            $phpTypeHolder instanceof ReflectionParameter ||
            $phpTypeHolder instanceof ReflectionProperty
        ) {
            /** @var list<ReflectionAttribute<Format>> $annotations */
            $annotations = $phpTypeHolder->getAttributes(Format::class);
            if (isset($annotations[0])) {
                $annotation = $annotations[0]->newInstance();
                $timestampFormat = $annotation->value;
            }
        }

        return [
            'type' => Type::OAS_TYPE_NAME_STRING,
            'format' => 'date-time',
            'example' => date($timestampFormat, 0),
        ];
    }

    public function getWeight(): int
    {
        return 0;
    }
}
