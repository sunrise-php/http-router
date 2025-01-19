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
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\StringPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\SymfonyUidPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\TimestampPhpTypeSchemaResolver;
use Sunrise\Http\Router\OpenApi\PhpTypeSchemaResolver\TimezonePhpTypeSchemaResolver;

use function sprintf;
use function usort;

/**
 * @since 3.0.0
 */
final class PhpTypeSchemaResolverChain implements PhpTypeSchemaResolverChainInterface
{
    private readonly OpenApiConfiguration $openApiConfiguration;

    /**
     * @var array<array-key, PhpTypeSchemaResolverInterface>
     */
    private array $phpTypeSchemaResolvers = [];

    /**
     * @var array<string, array<array-key, mixed>>
     */
    private array $namedPhpTypeSchemas = [];

    private bool $isPhpTypeResolversSorted = false;

    /**
     * @param array<array-key, PhpTypeSchemaResolverInterface> $phpTypeSchemaResolvers
     */
    public function __construct(
        OpenApiConfiguration $openApiConfiguration,
        array $phpTypeSchemaResolvers = [],
    ) {
        $this->openApiConfiguration = $openApiConfiguration;
        $this->setPhpTypeResolvers(self::getDefaultPhpTypeSchemaResolvers());
        $this->setPhpTypeResolvers($phpTypeSchemaResolvers);
    }

    public function resolvePhpTypeSchema(Type $phpType, Reflector $phpTypeHolder): array
    {
        $this->isPhpTypeResolversSorted or $this->sortPhpTypeResolvers();

        $phpTypeSchemaResolver = $this->findPhpTypeResolver($phpType, $phpTypeHolder);
        if ($phpTypeSchemaResolver === null) {
            return [];
        }

        $phpTypeSchemaName = null;
        if ($phpTypeSchemaResolver instanceof PhpTypeSchemaNameResolverInterface) {
            $phpTypeSchemaName = $phpTypeSchemaResolver->resolvePhpTypeSchemaName($phpType, $phpTypeHolder);
        }

        if (isset($phpTypeSchemaName, $this->namedPhpTypeSchemas[$phpTypeSchemaName])) {
            return self::completePhpTypeSchema($phpType, self::createPhpTypeSchemaRef($phpTypeSchemaName));
        }

        $phpTypeSchema = $phpTypeSchemaResolver->resolvePhpTypeSchema($phpType, $phpTypeHolder);

        if (isset($phpTypeSchemaName)) {
            $this->namedPhpTypeSchemas[$phpTypeSchemaName] = $phpTypeSchema;
            return self::completePhpTypeSchema($phpType, self::createPhpTypeSchemaRef($phpTypeSchemaName));
        }

        return self::completePhpTypeSchema($phpType, $phpTypeSchema);
    }

    /**
     * @inheritDoc
     */
    public function getNamedPhpTypeSchemas(): array
    {
        return $this->namedPhpTypeSchemas;
    }

    /**
     * @param array<array-key, PhpTypeSchemaResolverInterface> $phpTypeSchemaResolvers
     */
    private function setPhpTypeResolvers(array $phpTypeSchemaResolvers): void
    {
        foreach ($phpTypeSchemaResolvers as $phpTypeSchemaResolver) {
            $this->phpTypeSchemaResolvers[] = $phpTypeSchemaResolver;

            if ($phpTypeSchemaResolver instanceof OpenApiConfigurationAwareInterface) {
                $phpTypeSchemaResolver->setOpenApiConfiguration($this->openApiConfiguration);
            }
            if ($phpTypeSchemaResolver instanceof PhpTypeSchemaResolverChainAwareInterface) {
                $phpTypeSchemaResolver->setPhpTypeSchemaResolverChain($this);
            }
        }
    }

    private function sortPhpTypeResolvers(): void
    {
        $this->isPhpTypeResolversSorted = usort($this->phpTypeSchemaResolvers, static fn(
            PhpTypeSchemaResolverInterface $a,
            PhpTypeSchemaResolverInterface $b
        ): int => $b->getWeight() <=> $a->getWeight());
    }

    private function findPhpTypeResolver(Type $phpType, Reflector $phpTypeHolder): ?PhpTypeSchemaResolverInterface
    {
        foreach ($this->phpTypeSchemaResolvers as $phpTypeSchemaResolver) {
            if ($phpTypeSchemaResolver->supportsPhpType($phpType, $phpTypeHolder)) {
                return $phpTypeSchemaResolver;
            }
        }

        return null;
    }

    private static function createPhpTypeSchemaRef(string $phpTypeSchemaName): array
    {
        return ['$ref' => sprintf('#/components/schemas/%s', $phpTypeSchemaName)];
    }

    private static function completePhpTypeSchema(Type $phpType, array $phpTypeSchema): array
    {
        if ($phpType->allowsNull) {
            $phpTypeSchema['nullable'] = true;

            // https://swagger.io/docs/specification/v3_0/data-models/enums/#nullable-enums
            if (isset($phpTypeSchema['enum'])) {
                $phpTypeSchema['enum'][] = null;
            }
        }

        return $phpTypeSchema;
    }

    /**
     * @return array<array-key, PhpTypeSchemaResolverInterface>
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
            new StringPhpTypeSchemaResolver(),
            new SymfonyUidPhpTypeSchemaResolver(),
            new TimestampPhpTypeSchemaResolver(),
            new TimezonePhpTypeSchemaResolver(),
        ];
    }
}
