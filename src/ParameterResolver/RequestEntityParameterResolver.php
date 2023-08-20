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

namespace Sunrise\Http\Router\ParameterResolver;

use Doctrine\Persistence\ManagerRegistry as EntityManagerRegistryInterface;
use Generator;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestEntity;
use Sunrise\Http\Router\Exception\EntityNotFoundException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolutioner;

use function count;
use function current;
use function sprintf;

/**
 * RequestEntityParameterResolver
 *
 * @since 3.0.0
 */
final class RequestEntityParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param EntityManagerRegistryInterface $entityManagerRegistry
     * @param non-empty-string|null $defaultEntityManagerName
     */
    public function __construct(
        private EntityManagerRegistryInterface $entityManagerRegistry,
        private string|null $defaultEntityManagerName = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws EntityNotFoundException If an entity wasn't found.
     * @throws LogicException If the resolver is used incorrectly.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestEntity>> $attributes */
        $attributes = $parameter->getAttributes(RequestEntity::class);
        if ($attributes === []) {
            return;
        }

        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new LogicException(sprintf(
                'To use the #[RequestEntity] attribute, the parameter {%s} must be typed with an entity.',
                ParameterResolutioner::stringifyParameter($parameter),
            ));
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        $requestEntity = $attributes[0]->newInstance();

        $entityManagerName = $requestEntity->em ?? $this->defaultEntityManagerName;
        $entityManager = $this->entityManagerRegistry->getManager($entityManagerName);

        $entityIdentificationFieldName = $requestEntity->findBy;
        if ($entityIdentificationFieldName === null) {
            $entityMetadata = $entityManager->getClassMetadata($type->getName());
            $entityIdentificationFieldNames = $entityMetadata->getIdentifier();
            if (empty($entityIdentificationFieldNames) ||
                count($entityIdentificationFieldNames) > 1) {
                throw new LogicException(sprintf(
                    'To use the #[RequestEntity] attribute with the parameter {%s}, ' .
                    'it is necessary to explicitly set the "findBy" parameter within it, ' .
                    'as the entity {%s} either has a composite identifier or does not have one at all.',
                    ParameterResolutioner::stringifyParameter($parameter),
                    $type->getName(),
                ));
            }

            $entityIdentificationFieldName = current($entityIdentificationFieldNames);
        }

        $requestParameterName = $requestEntity->valueKey ?? $entityIdentificationFieldName;
        $entityIdentificationFieldValue = $context->getAttribute($requestParameterName);
        if ($entityIdentificationFieldValue === null) {
            throw new LogicException(sprintf(
                'To use the #[RequestEntity] attribute with the parameter {%s}, ' .
                'it might be necessary to explicitly set the "valueKey" parameter within it, ' .
                'as the attribute with the name "%s" was not found in the current request.',
                ParameterResolutioner::stringifyParameter($parameter),
                $requestParameterName,
            ));
        }

        $entity = $entityManager->getRepository($type->getName())->findOneBy([
            $entityIdentificationFieldName => $entityIdentificationFieldValue,
            ...$requestEntity->criteria,
        ]);

        if (isset($entity)) {
            yield $entity;
            return;
        }

        if ($parameter->allowsNull()) {
            yield null;
            return;
        }

        throw new EntityNotFoundException();
    }
}
