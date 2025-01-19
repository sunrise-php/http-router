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

use function sprintf;
use function usort;

/**
 * @since 3.0.0
 */
final class PhpTypeSchemaResolverChain implements PhpTypeSchemaResolverChainInterface
{
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
    public function __construct(array $phpTypeSchemaResolvers)
    {
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
            return self::createPhpTypeSchemaRef($phpTypeSchemaName);
        }

        $phpTypeSchema = $phpTypeSchemaResolver->resolvePhpTypeSchema($phpType, $phpTypeHolder);

        if (isset($phpTypeSchemaName)) {
            $this->namedPhpTypeSchemas[$phpTypeSchemaName] = $phpTypeSchema;
            return self::createPhpTypeSchemaRef($phpTypeSchemaName);
        }

        return $phpTypeSchema;
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
}
