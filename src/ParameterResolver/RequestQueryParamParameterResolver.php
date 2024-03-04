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
use Sunrise\Http\Router\Helper\HydratorHelper;
use Sunrise\Http\Router\Helper\ValidatorHelper;
use Sunrise\Http\Router\ServerRequest;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @since 3.0.0
 */
final class RequestQueryParamParameterResolver implements ParameterResolverInterface
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

        $request = ServerRequest::create($context);
        $requestParam = $annotations[0]->newInstance();

        if (!$request->hasQueryParam($requestParam->name)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw HttpException::queryParamMissed($requestParam->errorStatusCode, $requestParam->errorMessage)
                ->addMessagePlaceholder('{{ param_name }}', $requestParam->name);
        }

        try {
            $argument = $this->hydrator->castValue($request->getQueryParam($requestParam->name), Type::fromParameter($parameter), path: [$requestParam->name]);
        } catch (InvalidDataException|InvalidValueException $e) {
            throw HttpException::queryParamInvalid($requestParam->errorStatusCode, $requestParam->errorMessage, previous: $e)
                ->addMessagePlaceholder('{{ param_name }}', $requestParam->name)
                ->addConstraintViolation(...HydratorHelper::adaptConstraintViolations($e));
        }

        if (isset($this->validator)) {
            if (($constraints = ValidatorHelper::getParameterConstraints($parameter))->valid()) {
                if (($violations = $this->validator->validate($argument, [...$constraints]))->count() > 0) {
                    throw HttpException::queryParamInvalid($requestParam->errorStatusCode, $requestParam->errorMessage)
                        ->addMessagePlaceholder('{{ param_name }}', $requestParam->name)
                        ->addConstraintViolation(...ValidatorHelper::adaptConstraintViolations(...$violations));
                }
            }
        }

        yield $argument;
    }
}
