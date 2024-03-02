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
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\Constraint;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\ConstraintViolation;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function count;

/**
 * @since 3.0.0
 */
final class RequestCookieParameterResolver implements ParameterResolverInterface
{
    public function __construct(
        private readonly HydratorInterface $hydrator,
        private readonly ?ValidatorInterface $validator = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     *
     * @throws HttpException If a cookie was missed or invalid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestCookie>> $annotations */
        $annotations = $parameter->getAttributes(RequestCookie::class);
        if ($annotations === []) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.',
            );
        }

        $requestCookie = $annotations[0]->newInstance();

        $cookies = $context->getCookieParams();

        if (!isset($cookies[$requestCookie->name])) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw new HttpException($requestCookie->errorStatusCode, $requestCookie->errorMessage);
        }

        try {
            $value = $this->hydrator->castValue(
                $cookies[$requestCookie->name],
                Type::fromParameter($parameter),
                path: [$requestCookie->name],
            );
        } catch (InvalidDataException $e) {
            throw (new HttpException($requestCookie->errorStatusCode, $requestCookie->errorMessage, previous: $e))
                ->addConstraintViolation(...ConstraintViolation::fromHydrator(...$e->getExceptions()));
        } catch (InvalidValueException $e) {
            throw (new HttpException($requestCookie->errorStatusCode, $requestCookie->errorMessage, previous: $e))
                ->addConstraintViolation(...ConstraintViolation::fromHydrator($e));
        }

        if (isset($this->validator)) {
            $constraints = [];
            /** @var ReflectionAttribute<Constraint> $annotation */
            foreach ($parameter->getAttributes(Constraint::class) as $annotation) {
                $constraint = $annotation->newInstance();
                if ($constraint->value instanceof \Symfony\Component\Validator\Constraint) {
                    $constraints[] = $constraint->value;
                }
            }

            if (count($constraints) > 0 && count($violations = $this->validator->validate($value, $constraints)) > 0) {
                throw (new HttpException($requestCookie->errorStatusCode, $requestCookie->errorMessage))
                    ->addConstraintViolation(...ConstraintViolation::fromValidator(...$violations));
            }
        }

        yield $value;
    }
}
