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

namespace Sunrise\Http\Router\OpenApi;

use Reflector;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\ArrayAccessPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\ArrayPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\BackedEnumPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\BoolPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\FloatPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\IntPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\ObjectPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\RamseyUuidPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\StreamPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\StringPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\SymfonyUidPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\TimestampPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\TimezonePhpTypeSchemaResolver;

use function sprintf;
use function usort;

/**
 * @since 3.0.0
 */
final class OpenApiPhpTypeSchemaResolverManager implements OpenApiPhpTypeSchemaResolverManagerInterface
{
    /**
     * @var array<array-key, OpenApiPhpTypeSchemaResolverInterface>
     */
    private array $phpTypeSchemaResolvers = [];

    /**
     * @var array<string, array<array-key, mixed>>
     */
    private array $namedPhpTypeSchemas = [];

    private bool $isPhpTypeSchemaResolversSorted = false;

    /**
     * @param array<array-key, OpenApiPhpTypeSchemaResolverInterface> $phpTypeSchemaResolvers
     */
    public function __construct(
        private readonly OpenApiConfiguration $openApiConfiguration,
        array $phpTypeSchemaResolvers = [],
    ) {
        $this->setPhpTypeSchemaResolvers(self::getDefaultPhpTypeSchemaResolvers());
        $this->setPhpTypeSchemaResolvers($phpTypeSchemaResolvers);
    }

    /**
     * @inheritDoc
     */
    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->isPhpTypeSchemaResolversSorted or $this->sortPhpTypeSchemaResolvers();

        $phpTypeSchemaResolver = $this->findPhpTypeSchemaResolver($phpType, $phpTypeHolder);
        if ($phpTypeSchemaResolver === null) {
            return [];
        }

        $phpTypeSchemaName = null;
        if ($phpTypeSchemaResolver instanceof OpenApiPhpTypeSchemaNameResolverInterface) {
            $phpTypeSchemaName = $phpTypeSchemaResolver->resolvePhpTypeSchemaName($phpType, $phpTypeHolder);
        }

        if (isset($phpTypeSchemaName, $this->namedPhpTypeSchemas[$phpTypeSchemaName])) {
            $phpTypeSchemaReference = self::createPhpTypeSchemaReference($phpTypeSchemaName);
            return self::completePhpTypeSchema($phpType, $phpTypeSchemaReference);
        }

        $phpTypeSchema = $phpTypeSchemaResolver->resolvePhpTypeSchema($phpType, $phpTypeHolder);

        if (isset($phpTypeSchemaName)) {
            $this->namedPhpTypeSchemas[$phpTypeSchemaName] = $phpTypeSchema;
            $phpTypeSchemaReference = self::createPhpTypeSchemaReference($phpTypeSchemaName);
            return self::completePhpTypeSchema($phpType, $phpTypeSchemaReference);
        }

        return self::completePhpTypeSchema($phpType, $phpTypeSchema);
    }

    /**
     * @inheritDoc
     *
     * @see self::createPhpTypeSchemaReference()
     */
    public function enrichDocumentWithDefinitions(array &$document): void
    {
        foreach ($this->namedPhpTypeSchemas as $phpTypeSchemaName => $phpTypeSchema) {
            $document['components']['schemas'][$phpTypeSchemaName] = $phpTypeSchema;
        }
    }

    /**
     * @param array<array-key, OpenApiPhpTypeSchemaResolverInterface> $phpTypeSchemaResolvers
     */
    private function setPhpTypeSchemaResolvers(array $phpTypeSchemaResolvers): void
    {
        foreach ($phpTypeSchemaResolvers as $phpTypeSchemaResolver) {
            $this->phpTypeSchemaResolvers[] = $phpTypeSchemaResolver;

            if ($phpTypeSchemaResolver instanceof OpenApiConfigurationAwareInterface) {
                $phpTypeSchemaResolver->setOpenApiConfiguration($this->openApiConfiguration);
            }
            if ($phpTypeSchemaResolver instanceof OpenApiPhpTypeSchemaResolverManagerAwareInterface) {
                $phpTypeSchemaResolver->setOpenApiPhpTypeSchemaResolverManager($this);
            }
        }
    }

    private function findPhpTypeSchemaResolver(
        Type $phpType,
        Reflector $phpTypeHolder,
    ): ?OpenApiPhpTypeSchemaResolverInterface {
        foreach ($this->phpTypeSchemaResolvers as $phpTypeSchemaResolver) {
            if ($phpTypeSchemaResolver->supportsPhpType($phpType, $phpTypeHolder)) {
                return $phpTypeSchemaResolver;
            }
        }

        return null;
    }

    private function sortPhpTypeSchemaResolvers(): void
    {
        $this->isPhpTypeSchemaResolversSorted = usort($this->phpTypeSchemaResolvers, static fn(
            OpenApiPhpTypeSchemaResolverInterface $a,
            OpenApiPhpTypeSchemaResolverInterface $b
        ): int => $b->getWeight() <=> $a->getWeight());
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function createPhpTypeSchemaReference(string $phpTypeSchemaName): array
    {
        return [
            '$ref' => sprintf('#/components/schemas/%s', $phpTypeSchemaName),
        ];
    }

    /**
     * @param array<array-key, mixed> $phpTypeSchema
     *
     * @return array<array-key, mixed>
     */
    private static function completePhpTypeSchema(Type $phpType, array $phpTypeSchema): array
    {
        if ($phpType->allowsNull) {
            $phpTypeSchema = [
                'anyOf' => [
                    $phpTypeSchema,
                    [
                        'type' => Type::OAS_TYPE_NAME_NULL,
                    ],
                ],
            ];
        }

        return $phpTypeSchema;
    }

    /**
     * @return array<array-key, OpenApiPhpTypeSchemaResolverInterface>
     */
    private static function getDefaultPhpTypeSchemaResolvers(): array
    {
        return [
            new ArrayAccessPhpTypeSchemaResolver(),
            new ArrayPhpTypeSchemaResolver(),
            new BackedEnumPhpTypeSchemaResolver(),
            new BoolPhpTypeSchemaResolver(),
            new FloatPhpTypeSchemaResolver(),
            new IntPhpTypeSchemaResolver(),
            new ObjectPhpTypeSchemaResolver(),
            new RamseyUuidPhpTypeSchemaResolver(),
            new StreamPhpTypeSchemaResolver(),
            new StringPhpTypeSchemaResolver(),
            new SymfonyUidPhpTypeSchemaResolver(),
            new TimestampPhpTypeSchemaResolver(),
            new TimezonePhpTypeSchemaResolver(),
        ];
    }
}
