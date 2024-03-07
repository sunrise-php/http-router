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
use Sunrise\Http\Router\Annotation\RequestQueryParam;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\Helper\ValidatorHelper;
use Sunrise\Http\Router\ServerRequest;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolationProxy;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorConstraintViolationProxy;
use Sunrise\Hydrator\Exception\InvalidDataException;
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
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     *
     * @throws HttpException If a query parameter was missed or invalid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestQueryParam>> $annotations */
        $annotations = $parameter->getAttributes(RequestQueryParam::class);
        if ($annotations === []) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException('At this level of the application, any operations with the request are not possible.');
        }

        $serverRequest = ServerRequest::create($context);
        $processParams = $annotations[0]->newInstance();

        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;

        if (!$serverRequest->hasQueryParam($processParams->paramName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            }

            throw HttpExceptionFactory::queryParamMissed($errorStatusCode, $processParams->errorMessage)
                ->addMessagePlaceholder('{{ param_name }}', $processParams->paramName);
        }

        try {
            $argument = $this->hydrator->castValue(
                $serverRequest->getQueryParam($processParams->paramName),
                Type::fromParameter($parameter),
                path: [$processParams->paramName],
            );
        } catch (InvalidValueException|InvalidDataException $e) {
            $violations = ($e instanceof InvalidValueException) ? [$e] : $e->getExceptions();

            throw HttpExceptionFactory::queryParamInvalid($errorStatusCode, $processParams->errorMessage, previous: $e)
                ->addMessagePlaceholder('{{ param_name }}', $processParams->paramName)
                ->addConstraintViolation(...array_map(HydratorConstraintViolationProxy::create(...), $violations));
        }

        if (isset($this->validator)) {
            if (($constraints = ValidatorHelper::getParameterConstraints($parameter))->valid()) {
                if (($violations = $this->validator->validate($argument, [...$constraints]))->count() > 0) {
                    throw HttpExceptionFactory::queryParamInvalid($errorStatusCode, $processParams->errorMessage)
                        ->addMessagePlaceholder('{{ param_name }}', $processParams->paramName)
                        ->addConstraintViolation(...array_map(ValidatorConstraintViolationProxy::create(...), [...$violations]));
                }
            }
        }

        yield $argument;
    }
}
