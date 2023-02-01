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
use Sunrise\Http\Router\Exception\ResolvingParameterException;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Import functions
 */
use function class_exists;
use function sprintf;

/**
 * RequestEntityParameterResolver
 *
 * @since 3.0.0
 */
final class RequestEntityParameterResolver extends AbstractParameterResolver
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
     */
    public function resolveParameter(ReflectionParameter $parameter, $context)
    {
        /** @var ServerRequestInterface */
        $context = $context;

        /** @var ReflectionNamedType */
        $type = $parameter->getType();

        /** @var array{0: ReflectionAttribute} */
        $attributes = $parameter->getAttributes(RequestEntity::class);

        /** @var RequestEntity */
        $requestEntity = $attributes[0]->newInstance();

        /** @var mixed */
        $entityId = $context->getAttribute($requestEntity->paramKey);
        if (!isset($entityId)) {
            throw new ResolvingParameterException(sprintf(
                '{%s} Unable to get Entity ID (%s) by key %s',
                $this->stringifyParameter($parameter),
                $requestEntity->findBy,
                $requestEntity->paramKey
            ));
        }

        $entityName = $type->getName();
        if (!class_exists($entityName)) {
            throw new ResolvingParameterException(sprintf(
                '{%s} Entity %s does not exist',
                $this->stringifyParameter($parameter),
                $entityName
            ));
        }

        $entityManager = isset($requestEntity->em) ?
            $this->entityManagerRegistry->getManager($requestEntity->em) :
            $this->entityManagerRegistry->getManagerForClass($entityName);

        if (!isset($entityManager)) {
            throw new ResolvingParameterException(sprintf(
                '{%s} Unable to get Entity Manager for %s',
                $this->stringifyParameter($parameter),
                $entityName
            ));
        }

        $entityMetadata = $entityManager->getClassMetadata($entityName);
        if (!$entityMetadata->hasField($requestEntity->findBy)) {
            throw new ResolvingParameterException(sprintf(
                '{%s} Entity %s does not contain field %s',
                $this->stringifyParameter($parameter),
                $entityName,
                $requestEntity->findBy
            ));
        }

        $criteria = [
            $requestEntity->findBy => $entityId,
        ];

        $criteria += $requestEntity->criteria;

        $entity = $entityManager->getRepository($entityName)
            ->findOneBy($criteria);

        if (isset($entity)) {
            return $entity;
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw new EntityNotFoundException(sprintf(
            '%s Not Found',
            $entityMetadata->getReflectionClass()->getShortName()
        ));
    }
}
