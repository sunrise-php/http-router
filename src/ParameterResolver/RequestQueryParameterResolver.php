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
use Sunrise\Http\Router\Annotation\RequestQuery;
use Sunrise\Http\Router\Exception\InvalidRequestQueryException;
use Sunrise\Http\Router\Exception\UnhydrableObjectException;
use Sunrise\Http\Router\Exception\UnprocessableRequestQueryException;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\RequestQueryInterface;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
 * RequestQueryParameterResolver
 *
 * @link https://github.com/sunrise-php/hydrator
 * @link https://github.com/symfony/validator
 *
 * @since 3.0.0
 */
final class RequestQueryParameterResolver implements ParameterResolverInterface
{

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    /**
     * @var ValidatorInterface|null
     */
    private ?ValidatorInterface $validator;

    /**
     * @param HydratorInterface $hydrator
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        HydratorInterface $hydrator,
        ?ValidatorInterface $validator = null
    ) {
        $this->hydrator = $hydrator;
        $this->validator = $validator;
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

        if (8 === PHP_MAJOR_VERSION && $parameter->getAttributes(RequestQuery::class)) {
            return true;
        }

        if (is_subclass_of($parameter->getType()->getName(), RequestQueryInterface::class)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnhydrableObjectException
     *         If an object isn't valid.
     *
     * @throws InvalidRequestQueryException
     *         If the request query structure isn't valid.
     *
     * @throws UnprocessableRequestQueryException
     *         If the request query data isn't valid.
     */
    public function resolveParameter(ReflectionParameter $parameter, $context)
    {
        /** @var ServerRequestInterface */
        $context = $context;

        /** @var ReflectionNamedType */
        $type = $parameter->getType();

        try {
            $object = $this->hydrator->hydrate($type->getName(), $context->getQueryParams());
        } catch (InvalidObjectException $e) {
            throw new UnhydrableObjectException($e->getMessage(), 0, $e);
        } catch (InvalidValueException $e) {
            throw new InvalidRequestQueryException($e->getMessage(), 0, $e);
        }

        if (isset($this->validator)) {
            $violations = $this->validator->validate($object);
            if ($violations->count() > 0) {
                throw new UnprocessableRequestQueryException($violations);
            }
        }

        return $object;
    }
}
