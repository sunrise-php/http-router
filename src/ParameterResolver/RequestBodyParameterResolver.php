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

use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Exception\UnhydrableObjectException;
use Sunrise\Http\Router\Exception\UnprocessableRequestBodyException;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * RequestBodyParameterResolver
 *
 * @link https://github.com/sunrise-php/hydrator
 * @link https://github.com/symfony/validator
 *
 * @since 3.0.0
 */
final class RequestBodyParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param HydratorInterface $hydrator
     * @param ValidatorInterface|null $validator
     */
    public function __construct(private HydratorInterface $hydrator, private ?ValidatorInterface $validator = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function supportsParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): bool
    {
        if ($request === null) {
            return false;
        }

        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return false;
        }

        if ($parameter->getAttributes(RequestBody::class) === []) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     *
     * @throws UnhydrableObjectException
     *         If an object isn't valid.
     *
     * @throws UnprocessableRequestBodyException
     *         If the request's parsed body isn't valid.
     */
    public function resolveParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): mixed
    {
        /** @var ReflectionNamedType $type */
        $type = $parameter->getType();

        /** @var class-string $fqn */
        $fqn = $type->getName();

        try {
            $object = $this->hydrator->hydrate($fqn, (array) $request?->getParsedBody());
        } catch (InvalidObjectException $e) {
            throw new UnhydrableObjectException($e->getMessage(), 0, $e);
        } catch (InvalidDataException $e) {
            throw new UnprocessableRequestBodyException($e->getViolations());
        }

        if (isset($this->validator)) {
            $violations = $this->validator->validate($object);
            if ($violations->count() > 0) {
                throw new UnprocessableRequestBodyException($violations);
            }
        }

        return $object;
    }
}