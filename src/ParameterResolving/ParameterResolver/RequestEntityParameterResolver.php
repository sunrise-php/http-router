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

namespace Sunrise\Http\Router\ParameterResolving\ParameterResolver;

use Doctrine\Persistence\ManagerRegistry as EntityManagerRegistryInterface;
use Generator;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestEntity;
use Sunrise\Http\Router\Exception\Http\HttpNotFoundException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutioner;
use Sunrise\Http\Router\RouteInterface;

use function count;
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
     */
    public function __construct(private EntityManagerRegistryInterface $entityManagerRegistry)
    {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     *
     * @throws HttpNotFoundException If an entity wasn't found.
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

        $route = $context->getAttribute('@route');
        if (! $route instanceof RouteInterface) {
            throw new LogicException(sprintf(
                'The #[RequestEntity] attribute cannot be applied to the parameter {%s}, ' .
                'because the request does not contain information about the requested route, ' .
                'at least at this level of the application.',
                ParameterResolutioner::stringifyParameter($parameter),
            ));
        }

        $entityRequest = $attributes[0]->newInstance();
        $entityManagerNames = $this->entityManagerRegistry->getManagerNames();
        if (isset($entityRequest->em) && !isset($entityManagerNames[$entityRequest->em])) {
            throw new LogicException(sprintf(
                'The #[RequestEntity] attribute cannot be applied to the parameter {%s}, ' .
                'because the specified entity manager "%s" does not exist.',
                ParameterResolutioner::stringifyParameter($parameter),
                $entityRequest->em,
            ));
        }

        $entityName = $type->getName();
        $entityManager = $this->entityManagerRegistry->getManager($entityRequest->em);
        $entityMetadata = $entityManager->getClassMetadata($entityName);
        if (isset($entityRequest->findBy) && !$entityMetadata->hasField($entityRequest->findBy)) {
            throw new LogicException(sprintf(
                'The #[RequestEntity] attribute cannot be applied to the parameter {%s}, ' .
                'because the specified field "%s" does not exist.',
                ParameterResolutioner::stringifyParameter($parameter),
                $entityRequest->findBy,
            ));
        }

        $entityIdentifierFieldNames = $entityMetadata->getIdentifier();
        if (!isset($entityRequest->findBy) && count($entityIdentifierFieldNames) <> 1) {
            throw new LogicException(sprintf(
                'The #[RequestEntity] attribute cannot be applied to the parameter {%s}, ' .
                'because no entity search field name is provided, and automatic detection is not possible ' .
                'due to the entity having a composite identifier or no identifier at all. ' .
                'To resolve this issue, explicitly specify the field name within the attribute.',
                ParameterResolutioner::stringifyParameter($parameter),
            ));
        }

        $entitySearchFieldName = $entityRequest->findBy ?? $entityIdentifierFieldNames[0];
        $routePathVariableName = $entityRequest->pathVariable ?? $entitySearchFieldName;

        /** @var string|null $routePathVariableValue */
        $routePathVariableValue = $route->getAttribute($routePathVariableName);
        if (!isset($routePathVariableValue)) {
            throw new LogicException(sprintf(
                'The #[RequestEntity] attribute cannot be applied to the parameter {%s}, ' .
                'because the route is missing the value of variable "%s". ' .
                'To resolve this issue, explicitly specify the variable name within the attribute.',
                ParameterResolutioner::stringifyParameter($parameter),
                $routePathVariableName,
            ));
        }

        $entitySearchCriteria = $entityRequest->criteria;
        $entitySearchCriteria[$entitySearchFieldName] = $routePathVariableValue;
        $entityRepository = $entityManager->getRepository($entityName);
        $entity = $entityRepository->findOneBy($entitySearchCriteria);
        if (!isset($entity)) {
            throw new HttpNotFoundException();
        }

        yield $entity;
    }
}
