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
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Exception\Http\HttpUnprocessableEntityException;
use Sunrise\Http\Router\Exception\ParameterResolvingException;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\RequestBodyInterface;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Import functions
 */
use function is_subclass_of;

/**
 * Import constants
 */
use const PHP_MAJOR_VERSION;

/**
 * RequestBodyParameterResolver
 *
 * @link https://github.com/sunrise-php/hydrator
 *
 * @since 3.0.0
 */
final class RequestBodyParameterResolver implements ParameterResolverInterface
{

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    /**
     * @param HydratorInterface $hydrator
     */
    public function __construct(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsParameter(ReflectionParameter $parameter, $context): bool
    {
        if (!($context instanceof ServerRequestInterface)) {
            return false;
        }

        if (!($parameter->getType() instanceof ReflectionNamedType) || $parameter->getType()->isBuiltin()) {
            return false;
        }

        if (8 === PHP_MAJOR_VERSION && $parameter->getAttributes(RequestBody::class)) {
            return true;
        }

        if (is_subclass_of($parameter->getType()->getName(), RequestBodyInterface::class)) {
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
        $parameterType = $parameter->getType();

        try {
            return $this->hydrator->hydrate($parameterType->getName(), (array) $context->getParsedBody());
        } catch (InvalidObjectException $e) {
            throw new ParameterResolvingException($e->getMessage(), 0, $e);
        } catch (InvalidValueException $e) {
            throw new HttpUnprocessableEntityException($e->getMessage(), 0, $e);
        }
    }
}
