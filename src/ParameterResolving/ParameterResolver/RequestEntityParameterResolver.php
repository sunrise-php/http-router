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
use Sunrise\Http\Router\Dictionary\ErrorSource;
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

        $attribute = $attributes[0]->newInstance();

        $entityManager = $this->entityManagerRegistry->getManager($attribute->em);
        $entityMetadata = $entityManager->getClassMetadata($type->getName());
        if (isset($attribute->findBy) && !$entityMetadata->hasField($attribute->findBy)) {
            throw new LogicException(sprintf(
                'The #[RequestEntity] attribute cannot be applied to the parameter {%s}, ' .
                'because the specified field "%s" does not exist on the entity %s.',
                ParameterResolutioner::stringifyParameter($parameter),
                $attribute->findBy,
                $entityMetadata->getName(),
            ));
        }

        $entityIdentifierFieldNames = $entityMetadata->getIdentifier();
        if (!isset($attribute->findBy) && count($entityIdentifierFieldNames) <> 1) {
            throw new LogicException(sprintf(
                'The #[RequestEntity] attribute cannot be applied to the parameter {%s}, ' .
                'because the entity search field name is not provided and automatic detection is not possible ' .
                'due to the entity having a composite identifier or no identifier at all. ' .
                'To resolve this issue, explicitly specify the field name within the attribute.',
                ParameterResolutioner::stringifyParameter($parameter),
            ));
        }

        $entityFieldName = $attribute->findBy ?? $entityIdentifierFieldNames[0];
        $pathVariableName = $attribute->pathVariable ?? $entityFieldName;
        /** @var string|null $pathVariableValue */
        $pathVariableValue = $route->getAttribute($pathVariableName);
        if (!isset($pathVariableValue)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw new LogicException(sprintf(
                'The #[RequestEntity] attribute cannot be applied to the parameter {%s}, ' .
                'because the route is missing the value for the variable {%s}, most likely, ' .
                'because the variable is optional or its name is detected incorrectly. ' .
                'To resolve this issue, explicitly specify the variable name within the attribute, ' .
                'either make this parameter nullable or assign it a default value.',
                ParameterResolutioner::stringifyParameter($parameter),
                $pathVariableName,
            ));
        }

        $entity = $entityManager->getRepository($entityMetadata->getName())->findOneBy(
            [$entityFieldName => $pathVariableValue] + $attribute->criteria
        );

        if (!isset($entity)) {
            throw (new HttpNotFoundException)
                ->setSource(ErrorSource::CLIENT_REQUEST_PATH);
        }

        yield $entity;
    }
}
