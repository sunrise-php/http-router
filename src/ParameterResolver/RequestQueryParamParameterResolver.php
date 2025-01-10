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
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestQueryParam;
use Sunrise\Http\Router\Dictionary\PlaceholderCode;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\ServerRequest;
use Sunrise\Http\Router\Validation\Constraint\ArgumentConstraint;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolationAdapter;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorConstraintViolationAdapter;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_map;

/**
 * @since 3.0.0
 */
final class RequestQueryParamParameterResolver implements ParameterResolverInterface
{
    public function __construct(
        private readonly HydratorInterface $hydrator,
        private readonly ?ValidatorInterface $validator = null,
        private readonly ?int $defaultErrorStatusCode = null,
        private readonly ?string $defaultErrorMessage = null,
        private readonly bool $defaultValidationEnabled = true,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws HttpException
     * @throws InvalidObjectException
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        if (! $context instanceof ServerRequestInterface) {
            return;
        }

        /** @var list<ReflectionAttribute<RequestQueryParam>> $annotations */
        $annotations = $parameter->getAttributes(RequestQueryParam::class);
        if ($annotations === []) {
            return;
        }

        $request = ServerRequest::create($context);
        $processParams = $annotations[0]->newInstance();

        $queryParamName = $processParams->name;
        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;
        $errorMessage = $processParams->errorMessage ?? $this->defaultErrorMessage;

        if (!$request->hasQueryParam($queryParamName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            }

            throw HttpExceptionFactory::missingQueryParam($errorMessage, $errorStatusCode)
                ->addMessagePlaceholder(PlaceholderCode::QUERY_PARAM_NAME, $queryParamName);
        }

        try {
            $argument = $this->hydrator->castValue(
                $request->getQueryParam($queryParamName),
                Type::fromParameter($parameter),
                path: [$queryParamName],
            );
        } catch (InvalidValueException $e) {
            throw HttpExceptionFactory::invalidQueryParam($errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder(PlaceholderCode::QUERY_PARAM_NAME, $queryParamName)
                ->addConstraintViolation(new HydratorConstraintViolationAdapter($e));
        } catch (InvalidDataException $e) {
            throw HttpExceptionFactory::invalidQueryParam($errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder(PlaceholderCode::QUERY_PARAM_NAME, $queryParamName)
                ->addConstraintViolation(...array_map(
                    HydratorConstraintViolationAdapter::create(...),
                    $e->getExceptions(),
                ));
        }

        $validationEnabled = $processParams->validationEnabled ?? $this->defaultValidationEnabled;

        if ($this->validator !== null && $validationEnabled) {
            $violations = $this->validator
                ->startContext()
                ->atPath($queryParamName)
                ->validate($argument, new ArgumentConstraint($parameter))
                ->getViolations();

            if ($violations->count() > 0) {
                throw HttpExceptionFactory::invalidQueryParam($errorMessage, $errorStatusCode)
                    ->addMessagePlaceholder(PlaceholderCode::QUERY_PARAM_NAME, $queryParamName)
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
