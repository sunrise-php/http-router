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
use Sunrise\Http\Router\Annotation\RequestHeader;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Helper\HydratorHelper;
use Sunrise\Http\Router\Helper\ValidatorHelper;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
            throw new LogicException('At this level of the application, any operations with the request are not possible.');
        }

        $requestHeader = $annotations[0]->newInstance();

        if (!$context->hasHeader($requestHeader->name)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw HttpException::headerMissed($requestHeader->errorStatusCode, $requestHeader->errorMessage)
                ->addMessagePlaceholder('{{ header_name }}', $requestHeader->name);
        }

        try {
            $argument = $this->hydrator->castValue($context->getHeaderLine($requestHeader->name), Type::fromParameter($parameter), path: [$requestHeader->name]);
        } catch (InvalidDataException|InvalidValueException $e) {
            throw HttpException::headerInvalid($requestHeader->errorStatusCode, $requestHeader->errorMessage, previous: $e)
                ->addMessagePlaceholder('{{ header_name }}', $requestHeader->name)
                ->addConstraintViolation(...HydratorHelper::adaptConstraintViolations($e));
        }

        if (isset($this->validator)) {
            if (($constraints = ValidatorHelper::getParameterConstraints($parameter))->valid()) {
                if (($violations = $this->validator->validate($argument, [...$constraints]))->count() > 0) {
                    throw HttpException::headerInvalid($requestHeader->errorStatusCode, $requestHeader->errorMessage)
                        ->addMessagePlaceholder('{{ header_name }}', $requestHeader->name)
                        ->addConstraintViolation(...ValidatorHelper::adaptConstraintViolations(...$violations));
                }
            }
        }

        yield $argument;
    }
}
