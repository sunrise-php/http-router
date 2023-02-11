<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\ParameterResolver;

/**
 * Import classes
 */
use Doctrine\Persistence\ManagerRegistry as EntityManagerRegistryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Annotation\RequestEntity;
use Sunrise\Http\Router\Exception\EntityNotFoundException;
use Sunrise\Http\Router\Exception\MissingRequestParameterException;
use Sunrise\Http\Router\ParameterResolverInterface;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Import functions
 */
use function sprintf;

/**
 * RequestEntityParameterResolver
 *
 * @since 3.0.0
 */
final class RequestEntityParameterResolver implements ParameterResolverInterface
{

    /**
     * @var EntityManagerRegistryInterface
     */
    private EntityManagerRegistryInterface $entityManagerRegistry;

    /**
     * @param EntityManagerRegistryInterface $entityManagerRegistry
     */
    public function __construct(EntityManagerRegistryInterface $entityManagerRegistry)
    {
        $this->entityManagerRegistry = $entityManagerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsParameter(ReflectionParameter $parameter, $context): bool
    {
        if (!($context instanceof ServerRequestInterface)) {
            return false;
        }

        if (!($parameter->getType() instanceof ReflectionNamedType)) {
            return false;
        }

        if ($parameter->getType()->isBuiltin()) {
            return false;
        }

        if (8 === PHP_MAJOR_VERSION && $parameter->getAttributes(RequestEntity::class)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @throws MissingRequestParameterException
     *         If an entity ID was not found in the request parameters.
     *
     * @throws EntityNotFoundException
     *         If an entity was not found.
     */
    public function resolveParameter(ReflectionParameter $parameter, $context)
    {
        /** @var ServerRequestInterface */
        $context = $context;

        /** @var ReflectionNamedType */
        $parameterType = $parameter->getType();

        /** @var non-empty-list<ReflectionAttribute> */
        $parameterRequestEntityAttributes = $parameter->getAttributes(RequestEntity::class);

        /** @var RequestEntity */
        $requestEntity = $parameterRequestEntityAttributes[0]->newInstance();

        // if no request parameter key was assigned, the entity field name will be used...
        $requestParameterKey = $requestEntity->paramKey ?? $requestEntity->findBy;

        /** @var string|null */
        $entityId = $context->getAttribute($requestParameterKey);

        if (!isset($entityId)) {
            throw new MissingRequestParameterException(sprintf(
                'Missing the %s parameter in the request',
                $requestParameterKey
            ));
        }

        $criteria = $requestEntity->criteria;
        $criteria[$requestEntity->findBy] = $entityId;

        /** @var class-string */
        $entityName = $parameterType->getName();

        $entity = $this->entityManagerRegistry
            ->getManager($requestEntity->em)
            ->getRepository($entityName)
            ->findOneBy($criteria);

        if (isset($entity)) {
            return $entity;
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw new EntityNotFoundException('Entity Not Found');
    }
}
