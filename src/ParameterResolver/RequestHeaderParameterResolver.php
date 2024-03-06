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
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
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
        private readonly ?int $defaultErrorStatusCode = null,
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

        $processParams = $annotations[0]->newInstance();

        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;

        if (!$context->hasHeader($processParams->headerName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            }

            throw HttpExceptionFactory::headerMissed($errorStatusCode, $processParams->errorMessage)
                ->addMessagePlaceholder('{{ header_name }}', $processParams->headerName);
        }

        try {
            $argument = $this->hydrator->castValue(
                $context->getHeaderLine($processParams->headerName),
                Type::fromParameter($parameter),
                path: [$processParams->headerName],
            );
        } catch (InvalidDataException|InvalidValueException $e) {
            throw HttpExceptionFactory::headerInvalid($errorStatusCode, $processParams->errorMessage, previous: $e)
                ->addMessagePlaceholder('{{ header_name }}', $processParams->headerName)
                ->addConstraintViolation(...HydratorHelper::adaptConstraintViolations($e));
        }

        if (isset($this->validator)) {
            if (($constraints = ValidatorHelper::getParameterConstraints($parameter))->valid()) {
                if (($violations = $this->validator->validate($argument, [...$constraints]))->count() > 0) {
                    throw HttpExceptionFactory::headerInvalid($errorStatusCode, $processParams->errorMessage)
                        ->addMessagePlaceholder('{{ header_name }}', $processParams->headerName)
                        ->addConstraintViolation(...ValidatorHelper::adaptConstraintViolations(...$violations));
                }
            }
        }

        yield $argument;
    }
}
