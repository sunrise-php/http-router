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

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestQuery;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\Exception\UnhydrableObjectException;
use Sunrise\Http\Router\Exception\UnprocessableRequestQueryException;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * Constructor of the class
     *
     * @param HydratorInterface $hydrator
     * @param ValidatorInterface $validator
     */
    public function __construct(private HydratorInterface $hydrator, private ValidatorInterface $validator)
    {
    }

    /**
     * @inheritDoc
     *
     * @throws UnhydrableObjectException
     *         If an object isn't valid.
     *
     * @throws UnprocessableRequestQueryException
     *         If the request's query parameters isn't valid.
     *
     * @throws LogicException
     *         If the resolver is used incorrectly.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        if ($parameter->getAttributes(RequestQuery::class) === []) {
            return;
        }

        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new LogicException(
                'To use the #[RequestQuery] attribute, the parameter must be typed with a DTO.'
            );
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        try {
            $object = $this->hydrator->hydrate($type->getName(), $context->getQueryParams());
        } catch (InvalidObjectException $e) {
            throw new UnhydrableObjectException($e->getMessage(), 0, $e);
        } catch (InvalidDataException $e) {
            throw new UnprocessableRequestQueryException($e->getViolations());
        }

        $violations = $this->validator->validate($object);
        if ($violations->count() > 0) {
            throw new UnprocessableRequestQueryException($violations);
        }

        yield $object;
    }
}
