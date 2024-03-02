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
use Sunrise\Http\Router\Annotation\RequestHeader;
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
final class RequestHeaderParameterResolver implements ParameterResolverInterface
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
     * @throws HttpException If a header was missed or invalid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestHeader>> $annotations */
        $annotations = $parameter->getAttributes(RequestHeader::class);
        if ($annotations === []) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        $requestHeader = $annotations[0]->newInstance();

        if (!$context->hasHeader($requestHeader->name)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw new HttpException($requestHeader->errorStatusCode, $requestHeader->errorMessage);
        }

        try {
            $value = $this->hydrator->castValue(
                $context->getHeaderLine($requestHeader->name),
                Type::fromParameter($parameter),
                path: [$requestHeader->name],
            );
        } catch (InvalidDataException $e) {
            throw (new HttpException($requestHeader->errorStatusCode, $requestHeader->errorMessage, previous: $e))
                ->addConstraintViolation(...ConstraintViolation::fromHydrator(...$e->getExceptions()));
        } catch (InvalidValueException $e) {
            throw (new HttpException($requestHeader->errorStatusCode, $requestHeader->errorMessage, previous: $e))
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
                throw (new HttpException($requestHeader->errorStatusCode, $requestHeader->errorMessage))
                    ->addConstraintViolation(...ConstraintViolation::fromValidator(...$violations));
            }
        }

        yield $value;
    }
}
