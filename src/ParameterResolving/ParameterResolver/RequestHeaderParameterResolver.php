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

namespace Sunrise\Http\Router\ParameterResolving\ParameterResolver;

use Generator;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestHeader;
use Sunrise\Http\Router\Exception\Http\HttpBadRequestException;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolationProxy;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;

/**
 * RequestHeaderParameterResolver
 *
 * @since 3.0.0
 */
final class RequestHeaderParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param HydratorInterface $hydrator
     */
    public function __construct(private HydratorInterface $hydrator)
    {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     *
     * @throws HttpBadRequestException If a header was missed or invalid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestHeader>> $attributes */
        $attributes = $parameter->getAttributes(RequestHeader::class);
        if ($attributes === []) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        $attribute = $attributes[0]->newInstance();

        if (!$context->hasHeader($attribute->name)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw new HttpBadRequestException("The header {$attribute->name} must be provided.");
        }

        try {
            yield $this->hydrator->castValue(
                $context->getHeaderLine($attribute->name),
                Type::fromParameter($parameter),
                path: [$attribute->name],
            );
        } catch (InvalidDataException $e) {
            throw (new HttpBadRequestException(previous: $e))
                ->addConstraintViolation(...HydratorConstraintViolationProxy::create(...$e->getExceptions()));
        } catch (InvalidValueException $e) {
            throw (new HttpBadRequestException(previous: $e))
                ->addConstraintViolation(...HydratorConstraintViolationProxy::create($e));
        }
    }
}
