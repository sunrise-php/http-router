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
use Sunrise\Http\Router\Helper\ValidatorHelper;
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
     * @throws HttpException
     * @throws LogicException
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

        $headerName = $processParams->headerName;
        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;

        if (!$context->hasHeader($headerName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            }

            throw HttpExceptionFactory::headerRequired($processParams->errorMessage, $errorStatusCode)
                ->addMessagePlaceholder('{{ header_name }}', $headerName);
        }

        try {
            $argument = $this->hydrator->castValue($context->getHeaderLine($headerName), Type::fromParameter($parameter), path: [$headerName]);
        } catch (InvalidValueException|InvalidDataException $e) {
            throw HttpExceptionFactory::invalidHeader($processParams->errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder('{{ header_name }}', $headerName)
                ->addConstraintViolation(...array_map(HydratorConstraintViolationProxy::create(...), ($e instanceof InvalidValueException) ? [$e] : $e->getExceptions()));
        }

        if (isset($this->validator)) {
            if (($constraints = ValidatorHelper::getParameterConstraints($parameter))->valid()) {
                if (($violations = $this->validator->validate($argument, [...$constraints]))->count() > 0) {
                    throw HttpExceptionFactory::invalidHeader($processParams->errorMessage, $errorStatusCode)
                        ->addMessagePlaceholder('{{ header_name }}', $headerName)
                        ->addConstraintViolation(...array_map(ValidatorConstraintViolationProxy::create(...), [...$violations]));
                }
            }
        }

        yield $argument;
    }
}
