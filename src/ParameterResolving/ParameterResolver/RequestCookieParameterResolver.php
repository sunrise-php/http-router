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
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Dictionary\ErrorSource;
use Sunrise\Http\Router\Exception\Http\HttpBadRequestException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;

use function sprintf;

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
     */
    public function __construct(private HydratorInterface $hydrator)
    {
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
                'At this level of the application, any operations with the request are not possible.'
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

            throw (new HttpBadRequestException(sprintf('The cookie %s must be provided.', $attribute->name)))
                ->setSource(ErrorSource::CLIENT_REQUEST_COOKIE);
        }

        try {
            yield $this->hydrator->castValue(
                $cookies[$attribute->name],
                Type::fromParameter($parameter),
                path: [$attribute->name],
            );
        } catch (InvalidDataException $e) {
            throw (new HttpBadRequestException(previous: $e))
                ->setSource(ErrorSource::CLIENT_REQUEST_COOKIE)
                ->addHydratorViolation(...$e->getExceptions());
        } catch (InvalidValueException $e) {
            throw (new HttpBadRequestException(previous: $e))
                ->setSource(ErrorSource::CLIENT_REQUEST_COOKIE)
                ->addHydratorViolation($e);
        }
    }
}
