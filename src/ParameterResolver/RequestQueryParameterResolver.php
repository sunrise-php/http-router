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
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestQuery;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\Exception\InvalidParameterException;
use Sunrise\Http\Router\ParameterResolver;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolationProxy;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorConstraintViolationProxy;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_map;
use function sprintf;

/**
 * @since 3.0.0
 */
final class RequestQueryParameterResolver implements ParameterResolverInterface
{
    public function __construct(
        private readonly HydratorInterface $hydrator,
        private readonly ?ValidatorInterface $validator = null,
        private readonly ?int $defaultErrorStatusCode = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws HttpException
     * @throws InvalidObjectException
     * @throws InvalidParameterException
     */
    public function resolveParameter(ReflectionParameter $parameter, ?ServerRequestInterface $request): Generator
    {
        if ($request === null) {
            return;
        }

        /** @var list<ReflectionAttribute<RequestQuery>> $annotations */
        $annotations = $parameter->getAttributes(RequestQuery::class);
        if ($annotations === []) {
            return;
        }

        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new InvalidParameterException(sprintf(
                'To use the #[RequestQuery] annotation, the parameter %s must be typed with an object.',
                ParameterResolver::stringifyParameter($parameter),
            ));
        }

        $processParams = $annotations[0]->newInstance();

        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;

        try {
            $argument = $this->hydrator->hydrate($type->getName(), $request->getQueryParams());
        } catch (InvalidDataException $e) {
            throw HttpExceptionFactory::invalidQuery($processParams->errorMessage, $errorStatusCode, previous: $e)
                ->addConstraintViolation(...array_map(HydratorConstraintViolationProxy::create(...), $e->getExceptions()));
        }

        if (isset($this->validator)) {
            if (($violations = $this->validator->validate($argument))->count() > 0) {
                throw HttpExceptionFactory::invalidQuery($processParams->errorMessage, $errorStatusCode)
                    ->addConstraintViolation(...array_map(ValidatorConstraintViolationProxy::create(...), [...$violations]));
            }
        }

        yield $argument;
    }

    public function getWeight(): int
    {
        return 80;
    }
}
