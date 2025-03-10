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
use Sunrise\Http\Router\Annotation\RequestCookie;
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
final class RequestCookieParameterResolver implements ParameterResolverInterface
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
     * @throws InvalidObjectException
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        if (! $context instanceof ServerRequestInterface) {
            return;
        }

        /** @var list<ReflectionAttribute<RequestCookie>> $annotations */
        $annotations = $parameter->getAttributes(RequestCookie::class);
        if ($annotations === []) {
            return;
        }

        $request = ServerRequest::create($context);
        $requestCookieParams = $request->getCookieParams();
        $processParams = $annotations[0]->newInstance();
        $cookieName = $processParams->name;
        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;
        $errorMessage = $processParams->errorMessage ?? $this->defaultErrorMessage;
        $hydratorContext = $processParams->hydratorContext + $this->hydratorContext;
        $validationEnabled = $processParams->validationEnabled ?? $this->defaultValidationEnabled;

        if (!isset($requestCookieParams[$cookieName])) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            }

            throw HttpExceptionFactory::missingCookie($errorMessage, $errorStatusCode)
                ->addMessagePlaceholder(PlaceholderCode::COOKIE_NAME, $cookieName);
        }

        try {
            $argument = $this->hydrator->castValue(
                $requestCookieParams[$cookieName],
                Type::fromParameter($parameter),
                path: [$cookieName],
                context: $hydratorContext,
            );
        } catch (InvalidValueException $e) {
            throw HttpExceptionFactory::invalidCookie($errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder(PlaceholderCode::COOKIE_NAME, $cookieName)
                ->addConstraintViolation(new HydratorConstraintViolationAdapter($e));
        } catch (InvalidDataException $e) {
            throw HttpExceptionFactory::invalidCookie($errorMessage, $errorStatusCode, previous: $e)
                ->addMessagePlaceholder(PlaceholderCode::COOKIE_NAME, $cookieName)
                ->addConstraintViolation(...array_map(
                    HydratorConstraintViolationAdapter::create(...),
                    $e->getExceptions(),
                ));
        }

        if ($this->validator !== null && $validationEnabled) {
            $violations = $this->validator
                ->startContext()
                ->atPath($cookieName)
                ->validate($argument, new ArgumentConstraint($parameter))
                ->getViolations();

            if ($violations->count() > 0) {
                throw HttpExceptionFactory::invalidCookie($errorMessage, $errorStatusCode)
                    ->addMessagePlaceholder(PlaceholderCode::COOKIE_NAME, $cookieName)
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
