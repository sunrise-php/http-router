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
use Sunrise\Http\Router\Dictionary\PlaceholderCode;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\ParameterResolverInterface;
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
final class RequestHeaderParameterResolver implements ParameterResolverInterface
{
    public function __construct(
        private readonly HydratorInterface $hydrator,
        private readonly ?ValidatorInterface $validator = null,
        private readonly ?int $defaultErrorStatusCode = null,
        private readonly ?string $defaultErrorMessage = null,
        private readonly array $hydratorContext = [],
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

        /** @var list<ReflectionAttribute<RequestHeader>> $annotations */
        $annotations = $parameter->getAttributes(RequestHeader::class);
        if ($annotations === []) {
            return;
        }

        $processParams = $annotations[0]->newInstance();

        $headerName = $processParams->name;
        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;
        $errorMessage = $processParams->errorMessage ?? $this->defaultErrorMessage;
        $hydratorContext = $processParams->hydratorContext + $this->hydratorContext;
        $validationEnabled = $processParams->validationEnabled ?? $this->defaultValidationEnabled;

        if (!$context->hasHeader($headerName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            }

            throw HttpExceptionFactory::missingHeader($errorMessage, $errorStatusCode)
                ->addMessagePlaceholder(PlaceholderCode::HEADER_NAME, $headerName);
        }

        try {
            $argument = $this->hydrator->castValue(
                $context->getHeaderLine($headerName),
                Type::fromParameter($parameter),
                path: [$headerName],
                context: $hydratorContext,
            );
        } catch (InvalidValueException $e) {
            throw HttpExceptionFactory::invalidHeader($errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder(PlaceholderCode::HEADER_NAME, $headerName)
                ->addConstraintViolation(new HydratorConstraintViolationAdapter($e));
        } catch (InvalidDataException $e) {
            throw HttpExceptionFactory::invalidHeader($errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder(PlaceholderCode::HEADER_NAME, $headerName)
                ->addConstraintViolation(...array_map(
                    HydratorConstraintViolationAdapter::create(...),
                    $e->getExceptions(),
                ));
        }

        if ($this->validator !== null && $validationEnabled) {
            $violations = $this->validator
                ->startContext()
                ->atPath($headerName)
                ->validate($argument, new ArgumentConstraint($parameter))
                ->getViolations();

            if ($violations->count() > 0) {
                throw HttpExceptionFactory::invalidHeader($errorMessage, $errorStatusCode)
                    ->addMessagePlaceholder(PlaceholderCode::HEADER_NAME, $headerName)
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
