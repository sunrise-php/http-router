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
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
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
final class RequestCookieParameterResolver implements ParameterResolverInterface
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
     * @throws HttpException If a cookie was missed or invalid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestCookie>> $annotations */
        $annotations = $parameter->getAttributes(RequestCookie::class);
        if ($annotations === []) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException('At this level of the application, any operations with the request are not possible.');
        }

        $serverRequest = ServerRequest::create($context);
        $processParams = $annotations[0]->newInstance();

        $errorStatusCode = $processParams->errorStatusCode ?? $this->defaultErrorStatusCode;

        if (!$serverRequest->hasCookieParam($processParams->cookieName)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            }

            throw HttpExceptionFactory::cookieMissed($errorStatusCode, $processParams->errorMessage)
                ->addMessagePlaceholder('{{ cookie_name }}', $processParams->cookieName);
        }

        try {
            $argument = $this->hydrator->castValue(
                $serverRequest->getCookieParam($processParams->cookieName),
                Type::fromParameter($parameter),
                path: [$processParams->cookieName],
            );
        } catch (InvalidValueException|InvalidDataException $e) {
            throw HttpExceptionFactory::cookieInvalid($errorStatusCode, $processParams->errorMessage, previous: $e)
                ->addMessagePlaceholder('{{ cookie_name }}', $processParams->cookieName)
                ->addConstraintViolation(...HydratorHelper::adaptConstraintViolations($e));
        }

        if (isset($this->validator)) {
            if (($constraints = ValidatorHelper::getParameterConstraints($parameter))->valid()) {
                if (($violations = $this->validator->validate($argument, [...$constraints]))->count() > 0) {
                    throw HttpExceptionFactory::cookieInvalid($errorStatusCode, $processParams->errorMessage)
                        ->addMessagePlaceholder('{{ cookie_name }}', $processParams->cookieName)
                        ->addConstraintViolation(...ValidatorHelper::adaptConstraintViolations(...$violations));
                }
            }
        }

        yield $argument;
    }
}
