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
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestQuery;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\ParameterResolverChain;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolationAdapter;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorConstraintViolationAdapter;
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
        private readonly ?string $defaultErrorMessage = null,
        /** @var array<string, mixed> */
        private readonly array $hydratorContext = [],
        private readonly bool $defaultValidationEnabled = true,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws InvalidObjectException
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        if (! $context instanceof ServerRequestInterface) {
            return;
        }

        /** @var list<ReflectionAttribute<RequestQuery>> $annotations */
        $annotations = $parameter->getAttributes(RequestQuery::class);
        if ($annotations === []) {
            return;
        }

        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new InvalidArgumentException(sprintf(
                'To use the #[RequestQuery] annotation, the parameter "%s" must be typed with an object.',
                ParameterResolverChain::stringifyParameter($parameter),
            ));
        }

        /** @var class-string $className */
        $className = $type->getName();
        $processParams = $annotations[0]->newInstance();
        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;
        $errorMessage = $processParams->errorMessage ?? $this->defaultErrorMessage;
        $hydratorContext = $processParams->hydratorContext + $this->hydratorContext;
        $validationEnabled = $processParams->validationEnabled ?? $this->defaultValidationEnabled;

        try {
            $argument = $this->hydrator->hydrate($className, $context->getQueryParams(), context: $hydratorContext);
        } catch (InvalidDataException $e) {
            throw HttpExceptionFactory::invalidQuery($errorMessage, $errorStatusCode, previous: $e)
                ->addConstraintViolation(...array_map(
                    HydratorConstraintViolationAdapter::create(...),
                    $e->getExceptions(),
                ));
        }

        if ($this->validator !== null && $validationEnabled) {
            $violations = $this->validator->validate($argument);
            if ($violations->count() > 0) {
                throw HttpExceptionFactory::invalidQuery($errorMessage, $errorStatusCode)
                    ->addConstraintViolation(...array_map(
                        ValidatorConstraintViolationAdapter::create(...),
                        [...$violations],
                    ));
            }
        }

        yield $argument;
    }

    public function getWeight(): int
    {
        return 0;
    }
}
