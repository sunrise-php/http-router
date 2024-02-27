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
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Exception\Http\HttpUnprocessableEntityException;
use Sunrise\Http\Router\ParameterResolver;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolationProxy;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorConstraintViolationProxy;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function count;
use function sprintf;

/**
 * @link https://github.com/sunrise-php/hydrator
 * @link https://github.com/symfony/validator
 *
 * @since 3.0.0
 */
final class RequestBodyParameterResolver implements ParameterResolverInterface
{
    public function __construct(
        private readonly HydratorInterface $hydrator,
        private readonly ?ValidatorInterface $validator = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly or if an object isn't valid.
     *
     * @throws HttpUnprocessableEntityException If the request's parsed body isn't valid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $request): Generator
    {
        if ($parameter->getAttributes(RequestBody::class) === []) {
            return;
        }

        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new LogicException(sprintf(
                'To use the #[RequestBody] attribute, the parameter {%s} must be typed with an object.',
                ParameterResolver::stringifyParameter($parameter),
            ));
        }

        if (! $request instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        try {
            $object = $this->hydrator->hydrate($type->getName(), (array) $request->getParsedBody());
        } catch (InvalidDataException $e) {
            throw (new HttpUnprocessableEntityException)
                ->addConstraintViolation(...HydratorConstraintViolationProxy::create(...$e->getExceptions()));
        }

        $violations = $this->validator?->validate($object);
        if (count($violations ?? []) > 0) {
            throw (new HttpUnprocessableEntityException)
                ->addConstraintViolation(...ValidatorConstraintViolationProxy::create(...$violations));
        }

        yield $object;
    }
}
