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
use Sunrise\Http\Router\Exception\Http\HttpBadRequestException;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorErrorProxy;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorErrorProxy;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function count;

/**
 * RequestCookieParameterResolver
 *
 * @since 3.0.0
 */
final class RequestCookieParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param HydratorInterface $hydrator
     * @param ValidatorInterface|null $validator
     */
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
     * @throws HttpBadRequestException If a cookie was missed or invalid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestCookie>> $attributes */
        $attributes = $parameter->getAttributes(RequestCookie::class);
        if ($attributes === []) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.',
            );
        }

        $cookies = $context->getCookieParams();
        $attribute = $attributes[0]->newInstance();

        if (!isset($cookies[$attribute->name])) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw new HttpBadRequestException("The cookie {$attribute->name} must be provided.");
        }

        try {
            $value = $this->hydrator->castValue(
                $cookies[$attribute->name],
                Type::fromParameter($parameter),
                path: [$attribute->name],
            );
        } catch (InvalidDataException $e) {
            throw (new HttpBadRequestException(previous: $e))
                ->addError(...HydratorErrorProxy::create(...$e->getExceptions()));
        } catch (InvalidValueException $e) {
            throw (new HttpBadRequestException(previous: $e))
                ->addError(...HydratorErrorProxy::create($e));
        }

        $constraints = [];
        /** @var ReflectionAttribute<Constraint> $attribute */
        foreach ($parameter->getAttributes(Constraint::class) as $attribute) {
            $attribute = $attribute->newInstance();
            if ($attribute->value instanceof \Symfony\Component\Validator\Constraint) {
                $constraints[] = $attribute->value;
            }
        }

        if (count($constraints) > 0 && count($violations = $this->validator?->validate($value, $constraints) ?? []) > 0) {
            throw (new HttpBadRequestException())
                ->addError(...ValidatorErrorProxy::create(...$violations));
        }

        yield $value;
    }
}
