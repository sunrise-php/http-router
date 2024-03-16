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
use Sunrise\Http\Router\Annotation\RequestHeader;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\Helper\ValidatorHelper;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolation;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorConstraintViolation;
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
        private readonly ?string $defaultErrorMessage = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws HttpException
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        if (! $context instanceof ServerRequestInterface) {
            return;
        }

        /** @var list<ReflectionAttribute<RequestHeader>> $annotations */
        $annotations = $parameter->getAttributes(RequestHeader::class);
        if ($annotations === []) {
            return;
        }

        $processParams = $annotations[0]->newInstance();

        $headerName = $processParams->headerName;
        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;
        $errorMessage = $processParams->errorMessage ?? $this->defaultErrorMessage;

        if (!$context->hasHeader($headerName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            }

            throw HttpExceptionFactory::missingHeader($errorMessage, $errorStatusCode)
                ->addMessagePlaceholder('{{ header_name }}', $headerName);
        }

        try {
            $argument = $this->hydrator->castValue($context->getHeaderLine($headerName), Type::fromParameter($parameter), path: [$headerName]);
        } catch (InvalidValueException $e) {
            throw HttpExceptionFactory::invalidHeader($errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder('{{ header_name }}', $headerName)
                ->addConstraintViolation(HydratorConstraintViolation::create($e));
        } catch (InvalidDataException $e) {
            throw HttpExceptionFactory::invalidHeader($errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder('{{ header_name }}', $headerName)
                ->addConstraintViolation(...array_map(HydratorConstraintViolation::create(...), $e->getExceptions()));
        }

        if (isset($this->validator)) {
            if (($constraints = ValidatorHelper::getParameterConstraints($parameter))->valid()) {
                if (($violations = $this->validator->validate($argument, [...$constraints]))->count() > 0) {
                    throw HttpExceptionFactory::invalidHeader($errorMessage, $errorStatusCode)
                        ->addMessagePlaceholder('{{ header_name }}', $headerName)
                        ->addConstraintViolation(...array_map(ValidatorConstraintViolation::create(...), [...$violations]));
                }
            }
        }

        yield $argument;
    }

    public function getWeight(): int
    {
        return 20;
    }
}
